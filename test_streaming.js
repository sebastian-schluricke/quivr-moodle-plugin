/**
 * Test Script for Streaming Functionality in mybrainchat Plugin
 * 
 * This script can be used to test the streaming functionality in the browser console
 * when viewing a mybrainchat activity in Moodle.
 * 
 * Usage:
 * 1. Open a mybrainchat activity in Moodle
 * 2. Open the browser console (F12 or Ctrl+Shift+I)
 * 3. Copy and paste this entire script into the console
 * 4. Run the tests by calling the test functions
 */

// Configuration - These values will be automatically populated from the page
const testConfig = {
  brainId: null,
  apiKey: null,
  chatId: null,
  quivrApiUrl: 'https://api.quivr.esfl.io'
};

// Initialize the test configuration from the page
function initTestConfig() {
  // Try to get the brain ID and API key from the page
  try {
    // Look for the MyBrainChat instance
    const chatInstance = window.myBrainChatInstance;
    if (chatInstance) {
      testConfig.brainId = chatInstance.brainId;
      testConfig.apiKey = chatInstance.apiKey;
      testConfig.chatId = chatInstance.chatId;
      console.log('Test configuration initialized from page:', testConfig);
      return true;
    }
    
    // If we can't find the instance, try to extract from the script tag
    const scripts = document.querySelectorAll('script');
    for (const script of scripts) {
      const content = script.textContent;
      if (content && content.includes('initMyBrainChat')) {
        const brainIdMatch = content.match(/brainId\s*=\s*["']([^"']+)["']/);
        const apiKeyMatch = content.match(/apiKey\s*=\s*["']([^"']+)["']/);
        
        if (brainIdMatch) testConfig.brainId = brainIdMatch[1];
        if (apiKeyMatch) testConfig.apiKey = apiKeyMatch[1];
        
        if (testConfig.brainId && testConfig.apiKey) {
          console.log('Test configuration extracted from script tag:', testConfig);
          return true;
        }
      }
    }
    
    console.error('Could not initialize test configuration from page');
    return false;
  } catch (error) {
    console.error('Error initializing test configuration:', error);
    return false;
  }
}

// Create a chat session with the Quivr backend
async function createChatSession() {
  try {
    console.log('Creating chat session...');
    const response = await fetch(`${testConfig.quivrApiUrl}/chat`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${testConfig.apiKey}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: 'Test Streaming Chat',
        brain_id: testConfig.brainId
      })
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }
    
    const data = await response.json();
    testConfig.chatId = data.chat_id;
    console.log('Chat session created with ID:', testConfig.chatId);
    return testConfig.chatId;
  } catch (error) {
    console.error('Error creating chat session:', error);
    throw error;
  }
}

// Test streaming with a simple question
async function testSimpleQuestion() {
  if (!testConfig.chatId) {
    await createChatSession();
  }
  
  console.log('Testing simple question...');
  const question = 'What is a transformer?';
  
  try {
    await testStreamingQuestion(question);
    console.log('Simple question test completed');
  } catch (error) {
    console.error('Simple question test failed:', error);
  }
}

// Test streaming with a complex question
async function testComplexQuestion() {
  if (!testConfig.chatId) {
    await createChatSession();
  }
  
  console.log('Testing complex question...');
  const question = 'Explain the principles of electromagnetic induction and how they apply to transformers in detail.';
  
  try {
    await testStreamingQuestion(question);
    console.log('Complex question test completed');
  } catch (error) {
    console.error('Complex question test failed:', error);
  }
}

// Test streaming with a question that should generate follow-up questions
async function testFollowUpQuestion() {
  if (!testConfig.chatId) {
    await createChatSession();
  }
  
  console.log('Testing question with follow-up suggestions...');
  const question = 'What are the different types of transformers?';
  
  try {
    await testStreamingQuestion(question);
    console.log('Follow-up question test completed');
  } catch (error) {
    console.error('Follow-up question test failed:', error);
  }
}

// Test streaming with an error case
async function testErrorCase() {
  if (!testConfig.chatId) {
    await createChatSession();
  }
  
  console.log('Testing error case...');
  // Use an empty question to trigger an error
  const question = '';
  
  try {
    await testStreamingQuestion(question);
    console.log('Error case test completed');
  } catch (error) {
    console.log('Error case test completed with expected error:', error);
  }
}

// Helper function to test streaming with a specific question
async function testStreamingQuestion(question) {
  console.log(`Asking question: "${question}"`);
  
  try {
    const response = await fetch(`${testConfig.quivrApiUrl}/chat/${testConfig.chatId}/question/stream?brain_id=${testConfig.brainId}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${testConfig.apiKey}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        question: question
      })
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }
    
    console.log('Response received, content type:', response.headers.get('Content-Type'));
    
    // Check if the response is a streaming response
    if (response.headers.get('Content-Type')?.includes('text/event-stream')) {
      console.log('Detected streaming response, processing chunks...');
      
      const reader = response.body.getReader();
      const decoder = new TextDecoder();
      let fullMessage = '';
      let chunkCount = 0;
      
      while (true) {
        const { done, value } = await reader.read();
        
        if (done) {
          console.log('Stream complete, received', chunkCount, 'chunks');
          console.log('Final message length:', fullMessage.length);
          break;
        }
        
        const chunk = decoder.decode(value, { stream: true });
        chunkCount++;
        
        console.log(`Chunk ${chunkCount}, size: ${chunk.length} bytes`);
        
        // Try to extract data: prefix from streaming response
        const dataMatches = chunk.match(/data:\s*({.*?})\s*(?=data:|$)/gs);
        if (dataMatches && dataMatches.length > 0) {
          console.log(`Found ${dataMatches.length} data matches in chunk ${chunkCount}`);
          
          for (const dataMatch of dataMatches) {
            try {
              const jsonStartIndex = dataMatch.indexOf('{');
              const jsonStr = dataMatch.substring(jsonStartIndex).trim();
              const jsonData = JSON.parse(jsonStr);
              
              console.log('Parsed data match with keys:', Object.keys(jsonData).join(', '));
              
              if (jsonData.assistant !== undefined) {
                fullMessage += jsonData.assistant;
                console.log('Message length so far:', fullMessage.length);
              }
              
              if (jsonData.metadata && jsonData.metadata.followup_questions) {
                console.log('Found follow-up questions:', jsonData.metadata.followup_questions);
              }
            } catch (jsonError) {
              console.warn('Failed to parse data match as JSON:', jsonError);
            }
          }
        } else {
          console.log('No data matches found in chunk, raw content:', 
            chunk.length > 100 ? chunk.substring(0, 100) + '...' : chunk);
        }
      }
      
      return fullMessage;
    } else {
      console.log('Non-streaming response detected');
      const data = await response.json();
      console.log('Response data:', data);
      return data.fullMessage || data.assistant || JSON.stringify(data);
    }
  } catch (error) {
    console.error('Error testing streaming question:', error);
    throw error;
  }
}

// Run all tests
async function runAllTests() {
  if (!initTestConfig()) {
    console.error('Cannot run tests without proper configuration');
    return;
  }
  
  console.log('Running all streaming tests...');
  
  try {
    await testSimpleQuestion();
    await testComplexQuestion();
    await testFollowUpQuestion();
    await testErrorCase();
    
    console.log('All tests completed');
  } catch (error) {
    console.error('Test suite failed:', error);
  }
}

// Instructions for manual testing
console.log(`
Streaming Test Script loaded successfully!

To run tests, use the following commands:
- runAllTests() - Run all tests
- testSimpleQuestion() - Test with a simple question
- testComplexQuestion() - Test with a complex question
- testFollowUpQuestion() - Test with a question that should generate follow-up questions
- testErrorCase() - Test with an error case

Example: runAllTests()
`);

// Export test functions to global scope for easy access in console
window.testSimpleQuestion = testSimpleQuestion;
window.testComplexQuestion = testComplexQuestion;
window.testFollowUpQuestion = testFollowUpQuestion;
window.testErrorCase = testErrorCase;
window.runAllTests = runAllTests;