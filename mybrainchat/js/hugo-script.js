/**
 * Hugo-style chat script adapted for mybrainchat plugin
 * This script handles the chat UI and communication with the quivr backend
 *
 * Updated to use scoped chat tokens instead of exposing the API key.
 */

// Global reference to the chat instance
let myBrainChatInstance = null;

// Initialize the chat when the page loads
function initMyBrainChat(cmid, brainId, quivrApiUrl) {
  myBrainChatInstance = new MyBrainChat(cmid, brainId, quivrApiUrl);
  return myBrainChatInstance;
}

class MyBrainChat {
  /**
   * Constructor for the MyBrainChat class
   * @param {number} cmid - The course module ID
   * @param {string} brainId - The brain ID
   * @param {string} quivrApiUrl - The Quivr API URL
   */
  constructor(cmid, brainId, quivrApiUrl) {
    this.cmid = cmid;
    this.brainId = brainId;
    this.quivrApiUrl = quivrApiUrl || 'http://localhost:5050';
    this.chatId = null;
    this.questionAmount = 0;
    this.prefixId = 'answer_';

    // Token management
    this.chatToken = null;
    this.tokenExpiresAt = null;

    this.initializeChat();
  }

  /**
   * Initialize the chat UI and events
   */
  async initializeChat() {
    console.log('MyBrainChat Initialize');
    document.getElementById("chat_input").value = "Stelle Verbindung zum Brain her...";
    this.initializeEvents();

    // Try to restore chat_id from session
    await this.loadChatIdFromSession();

    // Enable the chat input
    let chat_input = document.getElementById("chat_input");
    let intro_text = document.getElementById("intro-text");
    intro_text.style.display = "block";
    if (this.chatId) {
      intro_text.textContent = "Chat wird fortgesetzt. Stelle eine Frage an Hugo.";
      console.log('Restored chat session:', this.chatId);
    } else {
      intro_text.textContent = "Willkommen! Stelle eine Frage an Hugo.";
    }
    chat_input.disabled = false;
    chat_input.focus();
    chat_input.value = "";
  }

  /**
   * Load chat_id and history from PHP session
   */
  async loadChatIdFromSession() {
    try {
      const response = await fetch(`${M.cfg.wwwroot}/mod/mybrainchat/api/session_chat.php?cmid=${this.cmid}`);
      const data = await response.json();
      if (data.success) {
        if (data.chat_id) {
          this.chatId = data.chat_id;
          console.log('Loaded chat_id from session:', this.chatId);
        }
        // Load and display chat history
        if (data.history && data.history.length > 0) {
          this.displayChatHistory(data.history);
          console.log('Loaded chat history:', data.history.length, 'messages');
        }
      }
    } catch (error) {
      console.warn('Could not load chat session:', error);
    }
  }

  /**
   * Display chat history from session
   * @param {Array} history - Array of message objects with role and content
   */
  displayChatHistory(history) {
    const chatView = document.getElementById("chat_history");

    history.forEach((msg, index) => {
      const displayDiv = document.createElement("div");

      if (msg.role === 'user') {
        displayDiv.className = "displayUser-container right-container";
        displayDiv.textContent = msg.content;
      } else if (msg.role === 'assistant') {
        displayDiv.className = "displayUser-container left-container";
        displayDiv.textContent = msg.content;
        // Set the question amount to track for new messages
        this.questionAmount = Math.floor(index / 2) + 1;
      }

      chatView.appendChild(displayDiv);
    });

    // Scroll to the bottom
    chatView.scrollTop = chatView.scrollHeight;
  }

  /**
   * Save chat_id to PHP session
   */
  async saveChatIdToSession() {
    if (!this.chatId) return;
    try {
      await fetch(`${M.cfg.wwwroot}/mod/mybrainchat/api/session_chat.php?cmid=${this.cmid}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ chat_id: this.chatId })
      });
      console.log('Saved chat_id to session:', this.chatId);
    } catch (error) {
      console.warn('Could not save chat_id to session:', error);
    }
  }

  /**
   * Save a message to the session history
   * @param {string} role - 'user' or 'assistant'
   * @param {string} content - The message content
   */
  async saveMessageToSession(role, content) {
    try {
      await fetch(`${M.cfg.wwwroot}/mod/mybrainchat/api/session_chat.php?cmid=${this.cmid}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message: { role, content }
        })
      });
      console.log('Saved message to session:', role);
    } catch (error) {
      console.warn('Could not save message to session:', error);
    }
  }

  /**
   * Start a new chat session (clears session and resets chatId)
   */
  async startNewChat() {
    try {
      // Clear session on server
      await fetch(`${M.cfg.wwwroot}/mod/mybrainchat/api/session_chat.php?cmid=${this.cmid}`, {
        method: 'DELETE'
      });
    } catch (error) {
      console.warn('Could not clear session:', error);
    }

    // Reset local state
    this.chatId = null;
    this.questionAmount = 0;

    // Clear chat history UI
    const chatHistory = document.getElementById("chat_history");
    chatHistory.innerHTML = '';

    // Update intro text
    const introText = document.getElementById("intro-text");
    introText.textContent = "Neuer Chat gestartet. Stelle eine Frage an Hugo.";

    // Focus input
    const chatInput = document.getElementById("chat_input");
    chatInput.focus();

    console.log('Started new chat session');
  }

  /**
   * Initialize event listeners
   */
  initializeEvents() {
    let close_ele = document.getElementById("open_confirm_box");
    if (typeof(close_ele) != 'undefined' && close_ele != null) {
      close_ele.addEventListener('click', (event) => {
        this.openConfirmBox();
      });
    }

    let confirm_close_ele = document.getElementById("confirm_close_chat_btn");
    if (typeof(confirm_close_ele) != 'undefined' && confirm_close_ele != null) {
      confirm_close_ele.addEventListener('click', (event) => {
        window.close();
      });
    }

    let cancel_confirm_ele = document.getElementById("cancel_confirm_box_btn");
    if (typeof(cancel_confirm_ele) != 'undefined' && cancel_confirm_ele != null) {
      cancel_confirm_ele.addEventListener('click', (event) => {
        this.cancelConfirmBox();
      });
    }

    let confirm_chat_input = document.getElementById("confirm_chat_input");
    if (typeof(confirm_chat_input) != 'undefined' && confirm_chat_input != null) {
      confirm_chat_input.addEventListener('click', (event) => {
        this.submitForm();
      });
    }

    let chat_input = document.getElementById("chat_input");
    if (typeof(chat_input) != 'undefined' && chat_input != null) {
      chat_input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
          this.submitForm();
        }
      });

      // Character counter
      chat_input.addEventListener('input', function (e) {
        const target = e.target;
        const maxLength = target.getAttribute('maxlength');
        const currentLength = target.value.length;
        document.getElementById('input_counter').innerHTML = `${currentLength}/${maxLength}`;
      });
    }
  }

  /**
   * Open the confirmation box
   */
  openConfirmBox() {
    let confirmBox = document.getElementById("confirmBox");
    confirmBox.style.display = "block";
  }

  /**
   * Close the confirmation box
   */
  cancelConfirmBox() {
    let confirmBox = document.getElementById("confirmBox");
    confirmBox.style.display = "none";
  }

  /**
   * Submit the form and send the question
   */
  submitForm() {
    var input = document.getElementById("chat_input").value;
    if (input !== "") {
      // Reset the character counter
      document.getElementById('input_counter').innerHTML = `0/${document.getElementById('chat_input').getAttribute('maxlength')}`;

      // Display the user's question
      var chatView = document.getElementById("chat_history");
      var displayDiv = document.createElement("div");
      displayDiv.className = "displayUser-container right-container";
      displayDiv.textContent = input;
      chatView.appendChild(displayDiv);

      // Save user message to session
      this.saveMessageToSession('user', input);

      // Ask the question
      this.askQuestion(input);

      // Clear the input field
      document.getElementById("chat_input").value = '';

      // Scroll to the bottom
      chatView.scrollTop = chatView.scrollHeight;
    }
  }

  /**
   * Ensure we have a valid chat token.
   * Fetches a new token from the Moodle backend if needed.
   * @returns {Promise<string>} The valid chat token
   */
  async ensureValidToken() {
    // Check if we have a valid token (with 30 second buffer before expiry)
    const now = Date.now();
    const bufferMs = 30 * 1000;

    if (this.chatToken && this.tokenExpiresAt && (this.tokenExpiresAt - bufferMs) > now) {
      console.log('Using existing chat token');
      return this.chatToken;
    }

    console.log('Fetching new chat token from Moodle backend...');

    try {
      const response = await fetch(`${M.cfg.wwwroot}/mod/mybrainchat/api/get_token.php?cmid=${this.cmid}`);
      const data = await response.json();

      if (!response.ok || !data.success) {
        throw new Error(data.error || 'Failed to get chat token');
      }

      this.chatToken = data.token;
      this.tokenExpiresAt = new Date(data.expires_at).getTime();

      console.log('Obtained new chat token, expires at:', new Date(this.tokenExpiresAt));
      return this.chatToken;

    } catch (error) {
      console.error('Error fetching chat token:', error);
      throw error;
    }
  }

  /**
   * Create a chat session with the Quivr backend
   * @returns {Promise<string>} The chat ID
   */
  async createChatSession() {
    try {
      const token = await this.ensureValidToken();

      const response = await fetch(`${this.quivrApiUrl}/chat`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          name: 'Moodle Chat',
          brain_id: this.brainId
        })
      });

      const data = await response.json();

      if (!data.chat_id) {
        throw new Error('Chat creation failed');
      }

      // Save chat_id to session for persistence across page reloads
      this.chatId = data.chat_id;
      await this.saveChatIdToSession();

      return data.chat_id;
    } catch (error) {
      console.error('Error creating chat session:', error);
      throw error;
    }
  }

  /**
   * Ask a question to the quivr backend
   * @param {string} question - The question to ask
   */
  async askQuestion(question) {
    this.createAnswerContainer();

    try {
      // Ensure we have a valid token
      const token = await this.ensureValidToken();

      // Step 1: Create a chat session if one doesn't exist yet
      if (!this.chatId) {
        this.chatId = await this.createChatSession();
        console.log('Created chat session with ID:', this.chatId);
      }

      // Step 2: Ask the question
      const response = await fetch(`${this.quivrApiUrl}/chat/${this.chatId}/question/stream?brain_id=${this.brainId}`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          question: question
        })
      });

      let finalAnswer = '';

      if (!response.ok) {
        // If we get a 401/403, the token might have expired - try to refresh
        if (response.status === 401 || response.status === 403) {
          console.log('Token expired, refreshing...');
          this.chatToken = null;
          this.tokenExpiresAt = null;
          const newToken = await this.ensureValidToken();

          // Retry the request with the new token
          const retryResponse = await fetch(`${this.quivrApiUrl}/chat/${this.chatId}/question/stream?brain_id=${this.brainId}`, {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${newToken}`,
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              question: question
            })
          });

          if (!retryResponse.ok) {
            throw new Error(`HTTP error! Status: ${retryResponse.status}`);
          }

          finalAnswer = await this.handleStreamingResponse(retryResponse);
        } else {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
      } else {
        finalAnswer = await this.handleStreamingResponse(response);
      }

      // Save assistant response to session
      if (finalAnswer) {
        this.saveMessageToSession('assistant', finalAnswer);
      }

      // Re-enable the input field
      document.getElementById("hugo-avatar").src = M.cfg.wwwroot + "/mod/mybrainchat/pix/avatar.svg";
      let chat_input = document.getElementById("chat_input");
      chat_input.disabled = false;
      chat_input.value = "";
      chat_input.focus();

      // Feedback buttons temporarily disabled - see GitHub issue for future implementation
      // this.createThumbs();

    } catch (error) {
      this.postAnswer("Fehler: " + error.message);
      document.getElementById("hugo-avatar").src = M.cfg.wwwroot + "/mod/mybrainchat/pix/avatar.svg";
      let chat_input = document.getElementById("chat_input");
      chat_input.disabled = false;
      chat_input.value = "";
      chat_input.focus();
    }

    this.questionAmount++;
  }

  /**
   * Handle the streaming response from the Quivr backend
   * @param {Response} response - The fetch response object
   * @returns {Promise<string>} The final message content
   */
  async handleStreamingResponse(response) {
    // Check if the response is a streaming response
    if (response.headers.get('Content-Type')?.includes('text/event-stream')) {
      console.log('Detected streaming response, processing chunks...');

      const reader = response.body.getReader();
      const decoder = new TextDecoder();
      let fullMessage = '';
      let lastChunk = null;
      let metadata = null;

      while (true) {
        const { done, value } = await reader.read();

        if (done) {
          console.log('Stream complete, final message length:', fullMessage.length);

          if (metadata && metadata.followup_questions) {
            this.postAnswer(fullMessage, metadata);
          } else if (lastChunk && lastChunk.metadata && lastChunk.metadata.followup_questions) {
            metadata = lastChunk.metadata;
            this.postAnswer(fullMessage, metadata);
          }
          return fullMessage; // Return the final message
        }

        const chunk = decoder.decode(value, { stream: true });

        try {
          const jsonChunk = JSON.parse(chunk);
          lastChunk = jsonChunk;

          if (jsonChunk.delta) {
            fullMessage += jsonChunk.delta;
            this.postAnswer(fullMessage);
          }

          if (jsonChunk.metadata && jsonChunk.metadata.followup_questions) {
            metadata = jsonChunk.metadata;
          }
        } catch (e) {
          // Try SSE format
          const dataMatches = chunk.match(/data:\s*({.*?})\s*(?=data:|$)/gs);

          if (dataMatches && dataMatches.length > 0) {
            for (const dataMatch of dataMatches) {
              try {
                const jsonStartIndex = dataMatch.indexOf('{');
                if (jsonStartIndex === -1) continue;

                const jsonStr = dataMatch.substring(jsonStartIndex).trim();
                const jsonData = JSON.parse(jsonStr);

                if (jsonData.assistant !== undefined) {
                  fullMessage += jsonData.assistant;
                  this.postAnswer(fullMessage);
                  lastChunk = jsonData;
                }

                if (jsonData.metadata) {
                  metadata = jsonData.metadata;
                }
              } catch (jsonError) {
                console.warn('Failed to parse SSE data chunk:', jsonError.message);
              }
            }
          } else {
            // Last resort - treat as plain text
            fullMessage += chunk;
            this.postAnswer(fullMessage);
          }
        }
      }
    } else {
      // Handle non-streaming response
      const data = await response.json();

      if (data.fullMessage) {
        if (data.metadata && data.metadata.followup_questions) {
          this.postAnswer(data.fullMessage, data.metadata);
        } else {
          this.postAnswer(data.fullMessage);
        }
        return data.fullMessage;
      } else if (data.error) {
        this.postAnswer("Fehler: " + data.error);
        return "Fehler: " + data.error;
      } else {
        this.postAnswer("Unerwartete Antwort vom Server.");
        return "Unerwartete Antwort vom Server.";
      }
    }
  }

  /**
   * Create the answer container
   */
  createAnswerContainer() {
    let chatView = document.getElementById("chat_history");
    document.getElementById("chat_input").disabled = true;
    document.getElementById("chat_input").value = '...';
    document.getElementById("hugo-avatar").src = M.cfg.wwwroot + "/mod/mybrainchat/pix/loading.svg";
    let displayDiv = document.createElement("div");
    displayDiv.id = this.prefixId + this.questionAmount;
    displayDiv.className = "displayUser-container left-container";
    chatView.appendChild(displayDiv);
  }

  /**
   * Post the answer to the chat
   * @param {string} answer - The answer to display
   * @param {Object} metadata - Optional metadata with follow-up questions
   */
  postAnswer(answer, metadata = null) {
    var chatContainer = document.getElementById("chat_history");
    var answerDiv = document.getElementById(this.prefixId + this.questionAmount);

    if (answerDiv) {
      answerDiv.textContent = answer;

      if (metadata && metadata.followup_questions && metadata.followup_questions.length > 0) {
        this.displayFollowUpQuestions(metadata.followup_questions);
      }

      chatContainer.scrollTop = chatContainer.scrollHeight;
    }
  }

  /**
   * Display follow-up questions as clickable elements
   * @param {Array} questions - The follow-up questions to display
   */
  displayFollowUpQuestions(questions) {
    let wrapperContainer = document.getElementById(this.prefixId + this.questionAmount);

    if (wrapperContainer && questions && questions.length > 0) {
      let followUpContainer = document.createElement('div');
      followUpContainer.className = 'followup-questions-container';

      let heading = document.createElement('div');
      heading.className = 'followup-heading';
      heading.textContent = 'Weitere Fragen:';
      followUpContainer.appendChild(heading);

      questions.forEach((question, index) => {
        let questionButton = document.createElement('button');
        questionButton.className = 'followup-question-button';
        questionButton.textContent = question;
        questionButton.dataset.question = question;

        questionButton.addEventListener('click', (event) => {
          const followUpQuestion = event.target.dataset.question;
          document.getElementById('chat_input').value = followUpQuestion;
          this.submitForm();
        });

        followUpContainer.appendChild(questionButton);
      });

      wrapperContainer.appendChild(followUpContainer);

      let chatView = document.getElementById("chat_history");
      chatView.scrollTop = chatView.scrollHeight;
    }
  }

  /**
   * Create the feedback buttons
   */
  createThumbs() {
    let wrapperContainer = document.getElementById(this.prefixId + this.questionAmount);

    if (wrapperContainer) {
      const feedbackContainer = document.createElement('div');
      feedbackContainer.className = 'feedback-container';

      const imageWrapperContainer = document.createElement('div');
      imageWrapperContainer.className = 'image-wrapper-container';

      const dislikeButton = document.createElement('div');
      dislikeButton.className = 'dislike-button';
      dislikeButton.dataset.id = this.questionAmount;

      const thumbDown = document.createElement('img');
      thumbDown.className = 'thumb-down';
      thumbDown.src = `${M.cfg.wwwroot}/mod/mybrainchat/pix/thumbs-down.svg`;

      const tooltipContainer = document.createElement('div');
      tooltipContainer.className = 'tooltip-container';
      tooltipContainer.textContent = 'Antwort ist nicht hilfreich!';

      dislikeButton.appendChild(thumbDown);
      imageWrapperContainer.appendChild(dislikeButton);
      imageWrapperContainer.appendChild(tooltipContainer);
      feedbackContainer.appendChild(imageWrapperContainer);

      wrapperContainer.appendChild(feedbackContainer);

      dislikeButton.addEventListener("click", function(event) {
        thumbDown.src = `${M.cfg.wwwroot}/mod/mybrainchat/pix/thumbs-down-grey.svg`;
        dislikeButton.style.pointerEvents = "none";
      });

      let chatView = document.getElementById("chat_history");
      chatView.scrollTop = chatView.scrollHeight;
    }
  }
}
