/**
 * Popup chat functionality for quivrchat plugin
 * This script handles opening the chat in a modal dialog
 */

// Initialize the popup chat functionality when the page loads
function initPopupChat(courseId) {
    console.log('Initializing popup chat for course: ' + courseId);
    
    // Create the modal dialog HTML structure
    createModalStructure();
    
    // Find the chat button and add click event listener
    const chatButton = document.getElementById('quivrchat-open-button');
    if (chatButton) {
        chatButton.addEventListener('click', function(event) {
            event.preventDefault();
            openChatPopup(courseId);
        });
    }
}

/**
 * Creates the HTML structure for the modal dialog
 */
function createModalStructure() {
    // Create the modal elements
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'quivrchat-modal-overlay';
    modalOverlay.id = 'quivrchat-modal-overlay';
    
    const modalContainer = document.createElement('div');
    modalContainer.className = 'quivrchat-modal-container';
    
    const modalClose = document.createElement('button');
    modalClose.className = 'quivrchat-modal-close';
    modalClose.innerHTML = '&times;';
    modalClose.setAttribute('aria-label', 'Close chat');
    modalClose.addEventListener('click', closeModal);
    
    const modalContent = document.createElement('iframe');
    modalContent.className = 'quivrchat-modal-content';
    modalContent.id = 'quivrchat-modal-content';
    modalContent.setAttribute('frameborder', '0');
    modalContent.setAttribute('title', 'Chat');
    
    // Add click event to close the modal when clicking outside the container
    modalOverlay.addEventListener('click', function(event) {
        if (event.target === modalOverlay) {
            closeModal();
        }
    });
    
    // Add escape key event to close the modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modalOverlay.classList.contains('active')) {
            closeModal();
        }
    });
    
    // Assemble the modal structure
    modalContainer.appendChild(modalClose);
    modalContainer.appendChild(modalContent);
    modalOverlay.appendChild(modalContainer);
    
    // Add the modal to the page
    document.body.appendChild(modalOverlay);
}

/**
 * Opens the chat in a modal dialog
 * @param {number} courseId - The course ID
 */
function openChatPopup(courseId) {
    // Get the base URL for the Moodle site
    const baseUrl = M.cfg.wwwroot;
    
    // Find the first available quivrchat instance in the course
    // This is done via AJAX to avoid having to pass all instance data to the page
    fetch(baseUrl + '/mod/quivrchat/api/get_instance.php?courseid=' + courseId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cmid) {
                // Get the modal elements
                const modalOverlay = document.getElementById('quivrchat-modal-overlay');
                const modalContent = document.getElementById('quivrchat-modal-content');
                
                if (!modalOverlay || !modalContent) {
                    console.error('Modal elements not found');
                    alert('Fehler beim Öffnen des Chats.');
                    return;
                }
                
                // Set the iframe source to the chat URL
                const chatUrl = baseUrl + '/mod/quivrchat/view.php?id=' + data.cmid + '&popup=1';
                modalContent.src = chatUrl;
                
                // Show the modal
                showModal();
            } else {
                console.error('No quivrchat instance found for this course');
                alert('Kein Chat für diesen Kurs verfügbar.');
            }
        })
        .catch(error => {
            console.error('Error fetching quivrchat instance:', error);
            alert('Fehler beim Laden des Chats.');
        });
}

/**
 * Shows the modal dialog
 */
function showModal() {
    const modalOverlay = document.getElementById('quivrchat-modal-overlay');
    if (modalOverlay) {
        // Remove any closing animation classes
        modalOverlay.classList.remove('closing');
        // Add active class to show the modal
        modalOverlay.classList.add('active');
        // Prevent scrolling on the body
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Closes the modal dialog
 */
function closeModal() {
    const modalOverlay = document.getElementById('quivrchat-modal-overlay');
    const modalContent = document.getElementById('quivrchat-modal-content');
    
    if (modalOverlay) {
        // Add closing animation class
        modalOverlay.classList.add('closing');
        
        // Wait for animation to complete before hiding
        setTimeout(() => {
            modalOverlay.classList.remove('active');
            modalOverlay.classList.remove('closing');
            // Restore scrolling on the body
            document.body.style.overflow = '';
            
            // Clear the iframe src after closing
            if (modalContent) {
                modalContent.src = '';
            }
        }, 300); // Match the animation duration (0.3s)
    }
}

// Export functions for use in other scripts
window.QuivrChatPopup = {
    init: initPopupChat,
    openChat: openChatPopup,
    closeChat: closeModal
};