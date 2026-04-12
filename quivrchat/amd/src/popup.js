/**
 * Popup chat module for mod_quivrchat.
 *
 * Handles the floating chat button on course pages and the modal
 * dialog that loads the chat activity in an iframe.
 *
 * @module     mod_quivrchat/popup
 * @copyright  2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax'], function(Ajax) {

    /** @type {Object} Localized strings (injected by hook_callbacks.php). */
    var strings = {
        error_opening_chat: 'Error opening chat.',
        error_no_chat_available: 'No chat available for this course.',
        error_loading_chat: 'Error loading chat.',
    };

    /**
     * Create the HTML structure for the modal dialog.
     */
    function createModalStructure() {
        var modalOverlay = document.createElement('div');
        modalOverlay.className = 'quivrchat-modal-overlay';
        modalOverlay.id = 'quivrchat-modal-overlay';

        var modalContainer = document.createElement('div');
        modalContainer.className = 'quivrchat-modal-container';

        var modalClose = document.createElement('button');
        modalClose.className = 'quivrchat-modal-close';
        modalClose.innerHTML = '&times;';
        modalClose.setAttribute('aria-label', 'Close chat');
        modalClose.addEventListener('click', closeModal);

        var modalContent = document.createElement('iframe');
        modalContent.className = 'quivrchat-modal-content';
        modalContent.id = 'quivrchat-modal-content';
        modalContent.setAttribute('frameborder', '0');
        modalContent.setAttribute('title', 'Chat');

        modalOverlay.addEventListener('click', function(event) {
            if (event.target === modalOverlay) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modalOverlay.classList.contains('active')) {
                closeModal();
            }
        });

        modalContainer.appendChild(modalClose);
        modalContainer.appendChild(modalContent);
        modalOverlay.appendChild(modalContainer);
        document.body.appendChild(modalOverlay);
    }

    /**
     * Open the chat in a modal dialog.
     *
     * @param {number} courseId The course ID.
     */
    function openChatPopup(courseId) {
        var baseUrl = M.cfg.wwwroot;

        Ajax.call([{
            methodname: 'mod_quivrchat_get_instance',
            args: {courseid: courseId}
        }])[0].then(function(data) {
            if (data.success && data.cmid) {
                var modalOverlay = document.getElementById('quivrchat-modal-overlay');
                var modalContent = document.getElementById('quivrchat-modal-content');

                if (!modalOverlay || !modalContent) {
                    window.console.error('Modal elements not found');
                    window.alert(strings.error_opening_chat);
                    return;
                }

                var chatUrl = baseUrl + '/mod/quivrchat/view.php?id=' + data.cmid + '&popup=1';
                modalContent.src = chatUrl;
                showModal();
            } else {
                window.console.error('No quivrchat instance found for this course');
                window.alert(strings.error_no_chat_available);
            }
        }).catch(function(error) {
            window.console.error('Error fetching quivrchat instance:', error);
            window.alert(strings.error_loading_chat);
        });
    }

    /**
     * Show the modal dialog.
     */
    function showModal() {
        var modalOverlay = document.getElementById('quivrchat-modal-overlay');
        if (modalOverlay) {
            modalOverlay.classList.remove('closing');
            modalOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Close the modal dialog.
     */
    function closeModal() {
        var modalOverlay = document.getElementById('quivrchat-modal-overlay');
        var modalContent = document.getElementById('quivrchat-modal-content');

        if (modalOverlay) {
            modalOverlay.classList.add('closing');
            setTimeout(function() {
                modalOverlay.classList.remove('active');
                modalOverlay.classList.remove('closing');
                document.body.style.overflow = '';
                if (modalContent) {
                    modalContent.src = '';
                }
            }, 300);
        }
    }

    return /** @alias module:mod_quivrchat/popup */ {
        /**
         * Initialize the popup chat for a course.
         *
         * @param {number} courseId The course ID.
         * @param {Object} localizedStrings Localized error strings from server.
         */
        init: function(courseId, localizedStrings) {
            if (localizedStrings) {
                strings = Object.assign(strings, localizedStrings);
            }
            window.console.log('QuivrChat popup: initializing for course ' + courseId);

            createModalStructure();

            var chatButton = document.getElementById('quivrchat-open-button');
            if (chatButton) {
                chatButton.addEventListener('click', function(event) {
                    event.preventDefault();
                    openChatPopup(courseId);
                });
            }
        }
    };
});
