/**
 * Hugo-style chat script adapted for mybrainchat plugin
 * This script handles the chat UI and communication with the quivr backend
 */

// Initialize the chat when the page loads
function initMyBrainChat(cmid, brainId, apiKey) {
  const myBrainChat = new MyBrainChat(cmid, brainId, apiKey);
}

class MyBrainChat {
  /**
   * Constructor for the MyBrainChat class
   * @param {number} cmid - The course module ID
   * @param {string} brainId - The brain ID
   * @param {string} apiKey - The API key for the Quivr backend
   */
  constructor(cmid, brainId, apiKey) {
    this.cmid = cmid;
    this.brainId = brainId;
    this.apiKey = apiKey;
    this.quivrApiUrl = 'https://api.quivr.esfl.io';
    this.chatId = null;
    this.questionAmount = 0;
    this.prefixId = 'answer_';
    this.initializeChat();
  }

  /**
   * Initialize the chat UI and events
   */
  initializeChat() {
    console.log('MyBrainChat Initialize');
    document.getElementById("chat_input").value = "Stelle Verbindung zum Brain her...";
    this.initializeEvents();
    
    // Enable the chat input
    let chat_input = document.getElementById("chat_input");
    let intro_text = document.getElementById("intro-text");
    intro_text.style.display = "block";
    intro_text.textContent = "Willkommen! Stelle eine Frage an das Brain.";
    chat_input.disabled = false;
    chat_input.focus();
    chat_input.value = "";
    
    // Set a random background image if available
    this.setRandomBackground();
  }

  /**
   * Set a random background image
   */
  setRandomBackground() {
    var images = [
      M.cfg.wwwroot + '/mod/mybrainchat/pix/background1.svg',
      M.cfg.wwwroot + '/mod/mybrainchat/pix/background2.svg'
    ];
    var randomIndex = Math.floor(Math.random() * images.length);
    var randomImage = images[randomIndex];
    var div = document.getElementById('background-container');
    if (div) {
      div.style.background = 'url(' + randomImage + ') no-repeat';
      div.style.backgroundSize = 'cover';
    }
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
      
      // Ask the question
      this.askQuestion(input);
      
      // Clear the input field
      document.getElementById("chat_input").value = '';
      
      // Scroll to the bottom
      chatView.scrollTop = chatView.scrollHeight;
    }
  }

  /**
   * Create a chat session with the Quivr backend
   * @returns {Promise<string>} The chat ID
   */
  async createChatSession() {
    try {
      const response = await fetch(`${this.quivrApiUrl}/chat`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.apiKey}`,
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
      // Step 1: Create a chat session if one doesn't exist yet
      if (!this.chatId) {
        this.chatId = await this.createChatSession();
        console.log('Created chat session with ID:', this.chatId);
      }
      
      // Step 2: Ask the question
      const response = await fetch(`${this.quivrApiUrl}/chat/${this.chatId}/question/stream?brain_id=${this.brainId}`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.apiKey}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          question: question
        })
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      // Check if the response is a streaming response by examining the Content-Type header
      // Quivr's streaming endpoint returns 'text/event-stream' content type
      if (response.headers.get('Content-Type')?.includes('text/event-stream')) {
        console.log('Detected streaming response, processing chunks...');
        
        // Handle streaming response using ReadableStream API
        // This allows us to process the response as it arrives, chunk by chunk
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let fullMessage = '';
        let lastChunk = null;
        let metadata = null;
        
        // Process the stream chunk by chunk until done
        while (true) {
          // Read the next chunk from the stream
          const { done, value } = await reader.read();
          
          // If the stream is complete
          if (done) {
            console.log('Stream complete, final message length:', fullMessage.length);
            
            // If we have metadata from the last chunk, make sure to display it
            // This is important because metadata often comes in the final chunk
            if (metadata && metadata.followup_questions) {
              console.log('Applying final metadata with follow-up questions');
              this.postAnswer(fullMessage, metadata);
            } else if (lastChunk && lastChunk.metadata && lastChunk.metadata.followup_questions) {
              console.log('Applying metadata from last chunk');
              metadata = lastChunk.metadata;
              this.postAnswer(fullMessage, metadata);
            }
            break;
          }
          
          // Decode the binary chunk to text using TextDecoder
          // The { stream: true } option tells the decoder that more chunks are coming
          const chunk = decoder.decode(value, { stream: true });
          
          try {
            // First, try to parse the entire chunk as a single JSON object
            // This works for standard JSON streaming where each chunk is a complete JSON object
            const jsonChunk = JSON.parse(chunk);
            
            // Store the last successfully parsed chunk to extract metadata at the end if needed
            lastChunk = jsonChunk;
            console.log('Parsed complete JSON chunk:', 
              Object.keys(jsonChunk).join(', '));
            
            // If the chunk contains a delta (incremental text), add it to our message
            if (jsonChunk.delta) {
              console.log('Found delta in chunk, length:', jsonChunk.delta.length);
              fullMessage += jsonChunk.delta;
              this.postAnswer(fullMessage);
            }
            
            // Check if this chunk has metadata with follow-up questions
            if (jsonChunk.metadata && jsonChunk.metadata.followup_questions) {
              console.log('Found metadata in JSON chunk with follow-up questions:', 
                jsonChunk.metadata.followup_questions.length);
              metadata = jsonChunk.metadata;
            }
          } catch (e) {
            // If parsing the entire chunk as JSON fails, we need to try alternative approaches
            // This is common with server-sent events (SSE) format where each line starts with "data: "
            console.warn('Failed to parse chunk as complete JSON, trying alternative parsing:', e.message);
            
            // APPROACH 1: Try to extract data: prefix from streaming response
            // This handles the SSE format used by many streaming APIs including Quivr
            // The pattern matches "data: {json}" format, handling multiple data chunks in a single response
            // It uses a non-greedy match and lookahead to properly separate multiple data chunks
            const dataMatches = chunk.match(/data:\s*({.*?})\s*(?=data:|$)/gs);
            
            if (dataMatches && dataMatches.length > 0) {
              // Log success and number of matches for debugging
              console.log(`Found ${dataMatches.length} SSE data matches in chunk`);
              
              // Process each data match separately
              for (const dataMatch of dataMatches) {
                try {
                  // Extract the JSON part (remove "data: " prefix)
                  // We use a more robust approach to extract the JSON by finding the first '{'
                  const jsonStartIndex = dataMatch.indexOf('{');
                  if (jsonStartIndex === -1) {
                    console.warn('No JSON object found in data match:', dataMatch);
                    continue;
                  }
                  
                  const jsonStr = dataMatch.substring(jsonStartIndex).trim();
                  const jsonData = JSON.parse(jsonStr);
                  
                  // Log the keys found in this data chunk
                  console.log('Parsed SSE data chunk with keys:', Object.keys(jsonData).join(', '));
                  
                  // Extract and add the assistant's message fragment
                  if (jsonData.assistant !== undefined) {
                    // Log the fragment for debugging
                    console.log('Processing assistant fragment:', 
                      jsonData.assistant.length > 20 
                        ? jsonData.assistant.substring(0, 20) + '...' 
                        : jsonData.assistant);
                    
                    fullMessage += jsonData.assistant;
                    this.postAnswer(fullMessage);
                    
                    // Store this as the last chunk in case it has metadata
                    lastChunk = jsonData;
                  }
                  
                  // Store metadata if available
                  if (jsonData.metadata) {
                    metadata = jsonData.metadata;
                    console.log('Found metadata in SSE chunk with follow-up questions:', 
                      metadata.followup_questions ? metadata.followup_questions.length : 'none');
                  }
                } catch (jsonError) {
                  console.warn('Failed to parse SSE data chunk as JSON:', jsonError.message, 
                    dataMatch.length > 50 ? dataMatch.substring(0, 50) + '...' : dataMatch);
                }
              }
            } 
            // APPROACH 2: If no data: prefixed chunks were found, try to extract JSON objects directly
            else {
              console.warn('No SSE data matches found, trying to extract JSON objects directly');
              
              // Try to find any JSON objects in the chunk using a regex pattern
              const jsonMatches = chunk.match(/{[^{}]*({[^{}]*})*[^{}]*}/g);
              
              if (jsonMatches && jsonMatches.length > 0) {
                console.log(`Found ${jsonMatches.length} direct JSON matches in chunk`);
                
                for (const jsonMatch of jsonMatches) {
                  try {
                    const jsonData = JSON.parse(jsonMatch);
                    console.log('Parsed direct JSON match with keys:', Object.keys(jsonData).join(', '));
                    
                    if (jsonData.assistant !== undefined) {
                      fullMessage += jsonData.assistant;
                      this.postAnswer(fullMessage);
                      lastChunk = jsonData;
                    }
                    
                    if (jsonData.metadata) {
                      metadata = jsonData.metadata;
                    }
                  } catch (jsonError) {
                    console.warn('Failed to parse direct JSON match:', jsonError.message);
                  }
                }
              } 
              // APPROACH 3: Last resort - treat the chunk as plain text
              else {
                console.warn('No JSON objects found in chunk, treating as plain text:', 
                  chunk.length > 100 ? chunk.substring(0, 100) + '...' : chunk);
                // Only append the chunk if no JSON objects were found
                // This is a fallback and should rarely happen
                fullMessage += chunk;
                this.postAnswer(fullMessage);
              }
            }
          }
        }
      } else {
        // Handle non-streaming response (standard JSON response)
        console.log('Detected non-streaming response, processing as JSON');
        
        try {
          // Try to parse the response as JSON
          const data = await response.json();
          console.log('Parsed non-streaming response with keys:', Object.keys(data).join(', '));
          
          // Process the response based on its content
          if (data.fullMessage) {
            console.log('Found fullMessage in response, length:', data.fullMessage.length);
            
            // Check if the response has metadata with follow-up questions
            if (data.metadata && data.metadata.followup_questions) {
              console.log('Found metadata with follow-up questions:', 
                data.metadata.followup_questions.length);
              this.postAnswer(data.fullMessage, data.metadata);
            } else {
              console.log('No follow-up questions in metadata');
              this.postAnswer(data.fullMessage);
            }
          } else if (data.error) {
            // Handle error responses
            console.error('Error in response:', data.error);
            this.postAnswer("❌ Fehler: " + data.error);
          } else {
            // Handle unexpected response format
            console.warn('Unexpected response format:', data);
            this.postAnswer("❌ Unerwartete Antwort vom Server.");
          }
        } catch (jsonError) {
          // If JSON parsing fails, try to get the response as text
          console.warn('Failed to parse non-streaming response as JSON:', jsonError.message);
          console.log('Trying to get response as text');
          
          const textResponse = await response.text();
          if (textResponse) {
            console.log('Received text response, length:', textResponse.length);
            
            // Try to extract metadata from the text response
            try {
              // Check if the response is a JSON string
              const jsonData = JSON.parse(textResponse);
              console.log('Successfully parsed text response as JSON with keys:', 
                Object.keys(jsonData).join(', '));
              
              if (jsonData.metadata && jsonData.metadata.followup_questions) {
                console.log('Found metadata with follow-up questions in text response');
                // Use assistant field if available, otherwise fullMessage, or fall back to the raw text
                this.postAnswer(jsonData.assistant || jsonData.fullMessage || textResponse, jsonData.metadata);
              } else {
                console.log('No follow-up questions in text response metadata');
                this.postAnswer(jsonData.assistant || jsonData.fullMessage || textResponse);
              }
            } catch (e) {
              // If parsing fails, just display the text response as-is
              console.warn('Failed to parse text response as JSON, displaying raw text');
              this.postAnswer(textResponse);
            }
          } else {
            // Handle empty text response
            console.error('Empty text response from server');
            this.postAnswer("❌ Fehler: Konnte die Antwort nicht verarbeiten.");
          }
        }
      }
      
      // Re-enable the input field
      document.getElementById("hugo-avatar").src = M.cfg.wwwroot + "/mod/mybrainchat/pix/avatar.svg";
      let chat_input = document.getElementById("chat_input");
      chat_input.disabled = false;
      chat_input.value = "";
      chat_input.focus();
      
      // Add feedback buttons
      this.createThumbs();
      
    } catch (error) {
      this.postAnswer("❌ Netzwerkfehler: " + error);
      document.getElementById("hugo-avatar").src = M.cfg.wwwroot + "/mod/mybrainchat/pix/avatar.svg";
      let chat_input = document.getElementById("chat_input");
      chat_input.disabled = false;
      chat_input.value = "";
      chat_input.focus();
    }
    
    this.questionAmount++;
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
      // Set the answer text
      answerDiv.textContent = answer;
      
      // If we have metadata with follow-up questions, display them
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
      // Create a container for the follow-up questions
      let followUpContainer = document.createElement('div');
      followUpContainer.className = 'followup-questions-container';
      
      // Add a heading
      let heading = document.createElement('div');
      heading.className = 'followup-heading';
      heading.textContent = 'Weitere Fragen:';
      followUpContainer.appendChild(heading);
      
      // Add each follow-up question as a button
      questions.forEach((question, index) => {
        let questionButton = document.createElement('button');
        questionButton.className = 'followup-question-button';
        questionButton.textContent = question;
        questionButton.dataset.question = question;
        
        // Add click event to send the follow-up question
        questionButton.addEventListener('click', (event) => {
          const followUpQuestion = event.target.dataset.question;
          document.getElementById('chat_input').value = followUpQuestion;
          this.submitForm();
        });
        
        followUpContainer.appendChild(questionButton);
      });
      
      // Append the follow-up container to the answer div
      wrapperContainer.appendChild(followUpContainer);
      
      // Scroll to show the follow-up questions
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
      // Create feedback container elements using DOM manipulation instead of innerHTML
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
      
      // Build the DOM structure
      dislikeButton.appendChild(thumbDown);
      imageWrapperContainer.appendChild(dislikeButton);
      imageWrapperContainer.appendChild(tooltipContainer);
      feedbackContainer.appendChild(imageWrapperContainer);
      
      // Append the feedback container to the wrapper container
      wrapperContainer.appendChild(feedbackContainer);
      
      // Add event listener to the dislike button
      dislikeButton.addEventListener("click", function(event) {
        thumbDown.src = `${M.cfg.wwwroot}/mod/mybrainchat/pix/thumbs-down-grey.svg`;
        dislikeButton.style.pointerEvents = "none";
        // In a future version, we could send feedback to the server
      });
      
      // Scroll to the bottom of the chat view
      let chatView = document.getElementById("chat_history");
      chatView.scrollTop = chatView.scrollHeight;
    }
  }
}