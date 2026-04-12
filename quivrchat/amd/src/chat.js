/**
 * Main chat module for mod_quivrchat.
 *
 * Handles the chat UI, streaming communication with the Quivr backend,
 * Markdown rendering, syntax highlighting, and session persistence.
 *
 * Vendor libraries (marked.js, DOMPurify, highlight.js) are loaded as
 * global scripts via $PAGE->requires->js() and accessed as window globals.
 *
 * @module     mod_quivrchat/chat
 * @copyright  2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {

    /** @type {QuivrChat|null} Module-level reference to the chat instance. */
    var chatInstance = null;

    /**
     * Configure marked.js for Markdown rendering.
     */
    function initializeMarkdownRenderer() {
        if (typeof window.marked !== 'undefined') {
            window.marked.setOptions({
                breaks: true,
                gfm: true,
                headerIds: false,
                mangle: false,
                highlight: function(code, lang) {
                    if (typeof window.hljs !== 'undefined' && lang && window.hljs.getLanguage(lang)) {
                        try {
                            return window.hljs.highlight(code, {language: lang}).value;
                        } catch (e) {
                            window.console.warn('Highlight.js error:', e);
                        }
                    }
                    return code.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                }
            });
        }
    }

    /**
     * Render Markdown to safe HTML.
     *
     * @param {string} markdown The markdown text to render.
     * @returns {string} Sanitized HTML string.
     */
    function renderMarkdown(markdown) {
        if (!markdown) {
            return '';
        }
        if (typeof window.marked === 'undefined') {
            return escapeHtml(markdown);
        }
        try {
            var html = window.marked.parse(markdown);
            if (typeof window.DOMPurify !== 'undefined') {
                return window.DOMPurify.sanitize(html, {
                    USE_PROFILES: {html: true},
                    ADD_ATTR: ['target'],
                    FORBID_TAGS: ['style', 'script', 'iframe', 'form', 'input']
                });
            }
            return html;
        } catch (e) {
            window.console.error('Markdown rendering error:', e);
            return escapeHtml(markdown);
        }
    }

    /**
     * Escape HTML special characters.
     *
     * @param {string} text Text to escape.
     * @returns {string} Escaped text.
     */
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Trigger MathJax typesetting on an element.
     *
     * @param {HTMLElement} element The element to typeset.
     */
    function typesetMath(element) {
        if (!element) {
            return;
        }
        if (typeof window.MathJax !== 'undefined' && window.MathJax.Hub) {
            window.MathJax.Hub.Queue(['Typeset', window.MathJax.Hub, element]);
        } else if (typeof window.MathJax !== 'undefined' && window.MathJax.typesetPromise) {
            window.MathJax.typesetPromise([element]).catch(function(err) {
                window.console.warn('MathJax typeset error:', err);
            });
        }
    }

    /**
     * Apply syntax highlighting to code blocks inside an element.
     *
     * @param {HTMLElement} contentDiv The element containing code blocks.
     */
    function highlightCode(contentDiv) {
        if (typeof window.hljs !== 'undefined') {
            contentDiv.querySelectorAll('pre code').forEach(function(block) {
                if (!block.classList.contains('hljs')) {
                    window.hljs.highlightElement(block);
                }
            });
        }
    }

    /**
     * The main QuivrChat class.
     *
     * @class
     * @param {number} cmid Course module ID.
     * @param {string} brainId Brain ID.
     * @param {string} quivrApiUrl Quivr API URL.
     * @param {Object} strings Localized strings.
     * @param {string} customInstructions Custom instructions for this activity.
     */
    function QuivrChat(cmid, brainId, quivrApiUrl, strings, customInstructions) {
        this.cmid = cmid;
        this.brainId = brainId;
        this.quivrApiUrl = quivrApiUrl || 'http://localhost:5050';
        this.chatId = null;
        this.questionAmount = 0;
        this.prefixId = 'answer_';
        this.customInstructions = customInstructions || null;
        this.strings = strings || {};
        this.chatToken = null;
        this.tokenExpiresAt = null;
        this._initializeChat();
    }

    QuivrChat.prototype._initializeChat = async function() {
        document.getElementById("chat_input").value = this.strings.connecting || '';
        this._initializeEvents();
        await this._loadChatIdFromSession();

        var chatInput = document.getElementById("chat_input");
        var introText = document.getElementById("intro-text");
        introText.style.display = "block";
        if (this.chatId) {
            introText.textContent = this.strings.chat_restored || '';
        } else {
            introText.textContent = this.strings.chat_welcome || '';
        }
        chatInput.disabled = false;
        chatInput.focus();
        chatInput.value = "";
    };

    QuivrChat.prototype._loadChatIdFromSession = async function() {
        try {
            var response = await fetch(M.cfg.wwwroot + '/mod/quivrchat/api/session_chat.php?cmid=' + this.cmid);
            var data = await response.json();
            if (data.success) {
                if (data.chat_id) {
                    this.chatId = data.chat_id;
                }
                if (data.history && data.history.length > 0) {
                    this._displayChatHistory(data.history);
                }
            }
        } catch (error) {
            window.console.warn('Could not load chat session:', error);
        }
    };

    QuivrChat.prototype._displayChatHistory = function(history) {
        var self = this;
        var chatView = document.getElementById("chat_history");
        history.forEach(function(msg, index) {
            var displayDiv = document.createElement("div");
            if (msg.role === 'user') {
                displayDiv.className = "displayUser-container right-container";
                displayDiv.textContent = msg.content;
            } else if (msg.role === 'assistant') {
                displayDiv.className = "displayUser-container left-container";
                var contentDiv = document.createElement('div');
                contentDiv.className = 'markdown-content';
                contentDiv.innerHTML = renderMarkdown(msg.content);
                displayDiv.appendChild(contentDiv);
                highlightCode(contentDiv);
                typesetMath(contentDiv);
                self.questionAmount = Math.floor(index / 2) + 1;
            }
            chatView.appendChild(displayDiv);
        });
        chatView.scrollTop = chatView.scrollHeight;
    };

    QuivrChat.prototype._saveChatIdToSession = async function() {
        if (!this.chatId) {
            return;
        }
        try {
            await fetch(M.cfg.wwwroot + '/mod/quivrchat/api/session_chat.php?cmid=' + this.cmid, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({chat_id: this.chatId})
            });
        } catch (error) {
            window.console.warn('Could not save chat_id to session:', error);
        }
    };

    QuivrChat.prototype._saveMessageToSession = async function(role, content) {
        try {
            await fetch(M.cfg.wwwroot + '/mod/quivrchat/api/session_chat.php?cmid=' + this.cmid, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({message: {role: role, content: content}})
            });
        } catch (error) {
            window.console.warn('Could not save message to session:', error);
        }
    };

    QuivrChat.prototype.startNewChat = async function() {
        try {
            await fetch(M.cfg.wwwroot + '/mod/quivrchat/api/session_chat.php?cmid=' + this.cmid, {
                method: 'DELETE'
            });
        } catch (error) {
            window.console.warn('Could not clear session:', error);
        }
        this.chatId = null;
        this.questionAmount = 0;
        document.getElementById("chat_history").innerHTML = '';
        document.getElementById("intro-text").textContent = this.strings.chat_new_started || '';
        document.getElementById("chat_input").focus();
    };

    QuivrChat.prototype._initializeEvents = function() {
        var self = this;

        var confirmInput = document.getElementById("confirm_chat_input");
        if (confirmInput) {
            confirmInput.addEventListener('click', function() {
                self._submitForm();
            });
        }

        var chatInput = document.getElementById("chat_input");
        if (chatInput) {
            chatInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    self._submitForm();
                }
            });
            chatInput.addEventListener('input', function(e) {
                var target = e.target;
                var maxLength = target.getAttribute('maxlength');
                var currentLength = target.value.length;
                document.getElementById('input_counter').innerHTML = currentLength + '/' + maxLength;
            });
        }
    };

    QuivrChat.prototype._submitForm = function() {
        var input = document.getElementById("chat_input").value;
        if (input !== "") {
            document.getElementById('input_counter').innerHTML =
                '0/' + document.getElementById('chat_input').getAttribute('maxlength');

            var chatView = document.getElementById("chat_history");
            var displayDiv = document.createElement("div");
            displayDiv.className = "displayUser-container right-container";
            displayDiv.textContent = input;
            chatView.appendChild(displayDiv);

            this._saveMessageToSession('user', input);
            this._askQuestion(input);
            document.getElementById("chat_input").value = '';
            chatView.scrollTop = chatView.scrollHeight;
        }
    };

    QuivrChat.prototype._ensureValidToken = async function() {
        var now = Date.now();
        var bufferMs = 30 * 1000;
        if (this.chatToken && this.tokenExpiresAt && (this.tokenExpiresAt - bufferMs) > now) {
            return this.chatToken;
        }
        var response = await fetch(M.cfg.wwwroot + '/mod/quivrchat/api/get_token.php?cmid=' + this.cmid);
        var data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Failed to get chat token');
        }
        this.chatToken = data.token;
        this.tokenExpiresAt = new Date(data.expires_at).getTime();
        return this.chatToken;
    };

    QuivrChat.prototype._createChatSession = async function() {
        var token = await this._ensureValidToken();
        var response = await fetch(this.quivrApiUrl + '/chat', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({name: 'Moodle Chat', brain_id: this.brainId})
        });
        var data = await response.json();
        if (!data.chat_id) {
            throw new Error('Chat creation failed');
        }
        this.chatId = data.chat_id;
        await this._saveChatIdToSession();
        return data.chat_id;
    };

    QuivrChat.prototype._askQuestion = async function(question) {
        var self = this;
        this._createAnswerContainer();
        try {
            var token = await this._ensureValidToken();
            if (!this.chatId) {
                this.chatId = await this._createChatSession();
            }

            var requestBody = {question: question};
            if (this.customInstructions) {
                requestBody.custom_instructions = this.customInstructions;
            }

            var url = this.quivrApiUrl + '/chat/' + this.chatId + '/question/stream?brain_id=' + this.brainId;
            var response = await fetch(url, {
                method: 'POST',
                headers: {'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json'},
                body: JSON.stringify(requestBody)
            });

            var finalAnswer = '';
            if (!response.ok) {
                if (response.status === 401 || response.status === 403) {
                    this.chatToken = null;
                    this.tokenExpiresAt = null;
                    var newToken = await this._ensureValidToken();
                    var retryResponse = await fetch(url, {
                        method: 'POST',
                        headers: {'Authorization': 'Bearer ' + newToken, 'Content-Type': 'application/json'},
                        body: JSON.stringify(requestBody)
                    });
                    if (!retryResponse.ok) {
                        throw new Error('HTTP error! Status: ' + retryResponse.status);
                    }
                    finalAnswer = await self._handleStreamingResponse(retryResponse);
                } else {
                    throw new Error('HTTP error! Status: ' + response.status);
                }
            } else {
                finalAnswer = await self._handleStreamingResponse(response);
            }

            if (finalAnswer) {
                this._saveMessageToSession('assistant', finalAnswer);
            }
            document.getElementById("quivr-avatar").src = M.cfg.wwwroot + "/mod/quivrchat/pix/avatar.svg";
            var chatInput = document.getElementById("chat_input");
            chatInput.disabled = false;
            chatInput.value = "";
            chatInput.focus();
        } catch (error) {
            self._postAnswer(self.strings.error_prefix + error.message);
            document.getElementById("quivr-avatar").src = M.cfg.wwwroot + "/mod/quivrchat/pix/avatar.svg";
            var chatInputErr = document.getElementById("chat_input");
            chatInputErr.disabled = false;
            chatInputErr.value = "";
            chatInputErr.focus();
        }
        this.questionAmount++;
    };

    QuivrChat.prototype._handleStreamingResponse = async function(response) {
        var self = this;
        var contentType = response.headers.get('Content-Type') || '';
        if (contentType.indexOf('text/event-stream') !== -1) {
            var reader = response.body.getReader();
            var decoder = new TextDecoder();
            var fullMessage = '';
            var lastChunk = null;
            var metadata = null;

            while (true) {
                var result = await reader.read();
                if (result.done) {
                    if (metadata && metadata.followup_questions) {
                        self._postAnswer(fullMessage, metadata);
                    } else if (lastChunk && lastChunk.metadata && lastChunk.metadata.followup_questions) {
                        self._postAnswer(fullMessage, lastChunk.metadata);
                    }
                    return fullMessage;
                }
                var chunk = decoder.decode(result.value, {stream: true});
                try {
                    var jsonChunk = JSON.parse(chunk);
                    lastChunk = jsonChunk;
                    if (jsonChunk.delta) {
                        fullMessage += jsonChunk.delta;
                        self._postAnswer(fullMessage);
                    }
                    if (jsonChunk.metadata && jsonChunk.metadata.followup_questions) {
                        metadata = jsonChunk.metadata;
                    }
                } catch (e) {
                    var dataMatches = chunk.match(/data:\s*(\{.*?\})\s*(?=data:|$)/gs);
                    if (dataMatches && dataMatches.length > 0) {
                        for (var i = 0; i < dataMatches.length; i++) {
                            try {
                                var jsonStartIndex = dataMatches[i].indexOf('{');
                                if (jsonStartIndex === -1) {
                                    continue;
                                }
                                var jsonStr = dataMatches[i].substring(jsonStartIndex).trim();
                                var jsonData = JSON.parse(jsonStr);
                                if (jsonData.assistant !== undefined) {
                                    fullMessage += jsonData.assistant;
                                    self._postAnswer(fullMessage);
                                    lastChunk = jsonData;
                                }
                                if (jsonData.metadata) {
                                    metadata = jsonData.metadata;
                                }
                            } catch (jsonError) {
                                window.console.warn('Failed to parse SSE data chunk:', jsonError.message);
                            }
                        }
                    } else {
                        fullMessage += chunk;
                        self._postAnswer(fullMessage);
                    }
                }
            }
        } else {
            var data = await response.json();
            if (data.fullMessage) {
                if (data.metadata && data.metadata.followup_questions) {
                    self._postAnswer(data.fullMessage, data.metadata);
                } else {
                    self._postAnswer(data.fullMessage);
                }
                return data.fullMessage;
            } else if (data.error) {
                self._postAnswer(self.strings.error_prefix + data.error);
                return self.strings.error_prefix + data.error;
            } else {
                self._postAnswer(self.strings.error_unexpected);
                return self.strings.error_unexpected;
            }
        }
    };

    QuivrChat.prototype._createAnswerContainer = function() {
        var chatView = document.getElementById("chat_history");
        document.getElementById("chat_input").disabled = true;
        document.getElementById("chat_input").value = '...';
        document.getElementById("quivr-avatar").src = M.cfg.wwwroot + "/mod/quivrchat/pix/loading.svg";
        var displayDiv = document.createElement("div");
        displayDiv.id = this.prefixId + this.questionAmount;
        displayDiv.className = "displayUser-container left-container";
        chatView.appendChild(displayDiv);
    };

    QuivrChat.prototype._postAnswer = function(answer, metadata) {
        var answerDiv = document.getElementById(this.prefixId + this.questionAmount);
        if (answerDiv) {
            var renderedHtml = renderMarkdown(answer);
            var contentDiv = answerDiv.querySelector('.markdown-content');
            if (!contentDiv) {
                contentDiv = document.createElement('div');
                contentDiv.className = 'markdown-content';
                answerDiv.appendChild(contentDiv);
            }
            contentDiv.innerHTML = renderedHtml;
            highlightCode(contentDiv);
            typesetMath(contentDiv);

            if (metadata && metadata.followup_questions && metadata.followup_questions.length > 0) {
                this._displayFollowUpQuestions(metadata.followup_questions);
            }
            document.getElementById("chat_history").scrollTop = document.getElementById("chat_history").scrollHeight;
        }
    };

    QuivrChat.prototype._displayFollowUpQuestions = function(questions) {
        var self = this;
        var wrapperContainer = document.getElementById(this.prefixId + this.questionAmount);
        if (wrapperContainer && questions && questions.length > 0) {
            var followUpContainer = document.createElement('div');
            followUpContainer.className = 'followup-questions-container';

            var heading = document.createElement('div');
            heading.className = 'followup-heading';
            heading.textContent = this.strings.followup_questions || 'Follow-up questions:';
            followUpContainer.appendChild(heading);

            questions.forEach(function(question) {
                var questionButton = document.createElement('button');
                questionButton.className = 'followup-question-button';
                questionButton.textContent = question;
                questionButton.dataset.question = question;
                questionButton.addEventListener('click', function(event) {
                    document.getElementById('chat_input').value = event.target.dataset.question;
                    self._submitForm();
                });
                followUpContainer.appendChild(questionButton);
            });

            wrapperContainer.appendChild(followUpContainer);
            document.getElementById("chat_history").scrollTop = document.getElementById("chat_history").scrollHeight;
        }
    };

    return /** @alias module:mod_quivrchat/chat */ {
        /**
         * Initialize the Quivr Chat for an activity.
         *
         * @param {number} cmid Course module ID.
         * @param {string} brainId Brain ID.
         * @param {string} quivrApiUrl Quivr API URL.
         * @param {Object} strings Localized strings.
         * @param {string} customInstructions Custom instructions for this activity.
         */
        init: function(cmid, brainId, quivrApiUrl, strings, customInstructions) {
            initializeMarkdownRenderer();
            chatInstance = new QuivrChat(cmid, brainId, quivrApiUrl, strings, customInstructions);

            var newChatBtn = document.getElementById('new_chat_btn');
            if (newChatBtn) {
                newChatBtn.addEventListener('click', function() {
                    if (chatInstance) {
                        chatInstance.startNewChat();
                    }
                });
            }
        }
    };
});
