/**
 * Quivr API Streaming Example for JavaScript
 * 
 * This example demonstrates how to use the Quivr API to create a streaming chat interface
 * using JavaScript. It shows two different approaches:
 * 1. Using the Fetch API with ReadableStream
 * 2. Using EventSource (Server-Sent Events)
 */

// Configuration - Replace these with your actual values
const API_URL = "https://sb.esfl.io/api";
const API_KEY = "your_api_key_here";
const BRAIN_ID = "your_brain_id_here";

// Headers for authentication
const headers = {
  "Authorization": `Bearer ${API_KEY}`,
  "Content-Type": "application/json"
};

/**
 * Step 1: Create a chat session
 * This must be done before querying the brain
 */
async function createChat() {
  try {
    const response = await fetch(`${API_URL}/chat`, {
      method: 'POST',
      headers: headers,
      body: JSON.stringify({
        name: "JavaScript Streaming Chat",
        brain_id: BRAIN_ID
      })
    });
    
    if (!response.ok) {
      throw new Error(`Error creating chat: ${response.status} ${response.statusText}`);
    }
    
    const data = await response.json();
    console.log("Chat created successfully:", data);
    return data.chat_id;
  } catch (error) {
    console.error("Failed to create chat:", error);
    throw error;
  }
}

/**
 * Approach 1: Using Fetch API with ReadableStream
 * This approach gives you more control over the stream processing
 */
async function streamWithFetch(chatId, question) {
  try {
    // Make the streaming request
    const response = await fetch(`${API_URL}/chat/${chatId}/question/stream?brain_id=${BRAIN_ID}`, {
      method: 'POST',
      headers: headers,
      body: JSON.stringify({ question }),
    });
    
    if (!response.ok) {
      throw new Error(`Error: ${response.status} ${response.statusText}`);
    }
    
    // Get the reader from the response body stream
    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let result = '';
    
    // Create a container for the streaming response
    const responseContainer = document.getElementById('response-container');
    responseContainer.innerHTML = '';
    
    // Function to process the stream chunks
    async function processStream() {
      try {
        while (true) {
          const { done, value } = await reader.read();
          
          if (done) {
            console.log("Stream complete");
            break;
          }
          
          // Decode the chunk and append to result
          const chunk = decoder.decode(value, { stream: true });
          result += chunk;
          
          // Update the UI with the latest chunk
          responseContainer.textContent = result;
        }
      } catch (error) {
        console.error("Error reading stream:", error);
        responseContainer.innerHTML += `<div class="error">Error: ${error.message}</div>`;
      }
    }
    
    // Start processing the stream
    await processStream();
    return result;
  } catch (error) {
    console.error("Stream request failed:", error);
    document.getElementById('response-container').innerHTML = 
      `<div class="error">Error: ${error.message}</div>`;
    throw error;
  }
}

/**
 * Approach 2: Using EventSource (Server-Sent Events)
 * This is simpler but requires the server to properly format SSE messages
 * Note: This approach may need adjustments based on the exact SSE format from Quivr
 */
function streamWithEventSource(chatId, question) {
  return new Promise((resolve, reject) => {
    // First, we need to create the URL with query parameters
    const url = new URL(`${API_URL}/chat/${chatId}/question/stream`);
    url.searchParams.append('brain_id', BRAIN_ID);
    
    // We can't set headers directly with EventSource, so we'll use fetch with eventsource-parser
    // For a real implementation, you might want to use a library like eventsource-parser
    
    // Create a container for the streaming response
    const responseContainer = document.getElementById('response-container');
    responseContainer.innerHTML = '';
    
    let result = '';
    
    // Make the request with fetch and process it as an event stream
    fetch(url, {
      method: 'POST',
      headers: headers,
      body: JSON.stringify({ question }),
    }).then(response => {
      if (!response.ok) {
        throw new Error(`Error: ${response.status} ${response.statusText}`);
      }
      
      const reader = response.body.getReader();
      const decoder = new TextDecoder();
      
      function read() {
        reader.read().then(({ done, value }) => {
          if (done) {
            console.log("Stream complete");
            resolve(result);
            return;
          }
          
          const chunk = decoder.decode(value, { stream: true });
          result += chunk;
          
          // Update the UI with the latest chunk
          responseContainer.textContent = result;
          
          // Continue reading
          read();
        }).catch(error => {
          console.error("Error reading stream:", error);
          responseContainer.innerHTML += `<div class="error">Error: ${error.message}</div>`;
          reject(error);
        });
      }
      
      read();
    }).catch(error => {
      console.error("Stream request failed:", error);
      responseContainer.innerHTML = `<div class="error">Error: ${error.message}</div>`;
      reject(error);
    });
  });
}

/**
 * Main function to demonstrate the streaming functionality
 */
async function demonstrateStreaming() {
  try {
    // Create a chat session
    const chatId = await createChat();
    
    // Get the question from the input field
    const question = document.getElementById('question-input').value;
    if (!question) {
      alert("Please enter a question");
      return;
    }
    
    // Get the selected streaming method
    const streamMethod = document.querySelector('input[name="stream-method"]:checked').value;
    
    // Show loading state
    document.getElementById('response-container').textContent = "Loading...";
    
    // Use the selected streaming method
    if (streamMethod === 'fetch') {
      await streamWithFetch(chatId, question);
    } else {
      await streamWithEventSource(chatId, question);
    }
  } catch (error) {
    console.error("Demonstration failed:", error);
    document.getElementById('response-container').innerHTML = 
      `<div class="error">Error: ${error.message}</div>`;
  }
}

// When the DOM is loaded, set up the UI
document.addEventListener('DOMContentLoaded', () => {
  // Create the UI elements if they don't exist
  if (!document.getElementById('streaming-demo-container')) {
    const container = document.createElement('div');
    container.id = 'streaming-demo-container';
    container.innerHTML = `
      <h2>Quivr API Streaming Demo</h2>
      
      <div class="input-group">
        <label for="question-input">Your question:</label>
        <input type="text" id="question-input" placeholder="Ask something...">
      </div>
      
      <div class="radio-group">
        <label>
          <input type="radio" name="stream-method" value="fetch" checked>
          Use Fetch API
        </label>
        <label>
          <input type="radio" name="stream-method" value="eventsource">
          Use EventSource
        </label>
      </div>
      
      <button id="submit-button">Ask Question</button>
      
      <div class="response-wrapper">
        <h3>Response:</h3>
        <div id="response-container" class="response"></div>
      </div>
    `;
    
    document.body.appendChild(container);
    
    // Add event listener to the button
    document.getElementById('submit-button').addEventListener('click', demonstrateStreaming);
  }
});

/**
 * For Node.js environments (without a DOM), you can use this function
 * to test the streaming functionality
 */
async function testStreamingInNode() {
  // This requires the node-fetch package for Node.js environments
  // npm install node-fetch
  const fetch = require('node-fetch');
  
  try {
    // Create a chat session
    console.log("Creating chat...");
    const createResponse = await fetch(`${API_URL}/chat`, {
      method: 'POST',
      headers: headers,
      body: JSON.stringify({
        name: "Node.js Streaming Test",
        brain_id: BRAIN_ID
      })
    });
    
    if (!createResponse.ok) {
      throw new Error(`Error creating chat: ${createResponse.status} ${createResponse.statusText}`);
    }
    
    const chatData = await createResponse.json();
    const chatId = chatData.chat_id;
    console.log(`Chat created with ID: ${chatId}`);
    
    // Make the streaming request
    const question = "What is artificial intelligence?";
    console.log(`Asking question: "${question}"`);
    
    const streamResponse = await fetch(`${API_URL}/chat/${chatId}/question/stream?brain_id=${BRAIN_ID}`, {
      method: 'POST',
      headers: headers,
      body: JSON.stringify({ question }),
    });
    
    if (!streamResponse.ok) {
      throw new Error(`Error: ${streamResponse.status} ${streamResponse.statusText}`);
    }
    
    // Process the stream
    console.log("Response:");
    
    // For Node.js, we need to handle the stream differently
    streamResponse.body.on('data', (chunk) => {
      process.stdout.write(chunk.toString());
    });
    
    streamResponse.body.on('end', () => {
      console.log("\n\nStream complete");
    });
    
    streamResponse.body.on('error', (err) => {
      console.error("Stream error:", err);
    });
    
  } catch (error) {
    console.error("Test failed:", error);
  }
}

// If running in Node.js environment
if (typeof window === 'undefined') {
  testStreamingInNode().catch(console.error);
}