<!-- Gemini Chatbot Component -->
<div id="gemini-chatbot-container">
  <!-- Chatbot Toggle Button (Floating) -->
  <button id="chatbot-toggle-btn" class="chatbot-toggle-btn" aria-label="Open Chatbot">
    <i data-feather="message-circle" style="width: 24px; height: 24px;"></i>
  </button>

  <!-- Chatbot Window -->
  <div id="chatbot-window" class="chatbot-window" style="display: none;">
    <div class="chatbot-header">
      <div class="d-flex align-items-center">
        <div class="chatbot-avatar">
          <i data-feather="bot" style="width: 20px; height: 20px;"></i>
        </div>
        <div class="ms-2">
          <h6 class="mb-0 fw-semibold">G14 AI Assistant</h6>
          <small class="text-muted">Smart Assistant</small>
        </div>
      </div>
      <button id="chatbot-close-btn" class="btn btn-sm btn-link p-0" aria-label="Close Chatbot">
        <i data-feather="x" style="width: 20px; height: 20px;"></i>
      </button>
    </div>

    <div class="chatbot-body" id="chatbot-messages">
      <div class="chatbot-message chatbot-message-bot">
        <div class="message-avatar">
          <i data-feather="bot" style="width: 16px; height: 16px;"></i>
        </div>
        <div class="message-content">
          <p class="mb-0">Hello! I'm the AI assistant for the G14 Inventory system. I can help you with:</p>
          <ul class="mb-0 mt-2 ps-3">
            <li>Guide you on how to use the system</li>
            <li>Answer questions about data (based on your permissions)</li>
            <li>Answer questions about features</li>
          </ul>
          <p class="mb-0 mt-2">Please ask a question to get started!</p>
        </div>
      </div>
    </div>

    <div class="chatbot-footer">
      <form id="chatbot-form" class="d-flex gap-2">
        <input
          type="text"
          id="chatbot-input"
          class="form-control form-control-sm"
          placeholder="Enter your question..."
          autocomplete="off"
        />
        <button type="submit" class="btn btn-primary btn-sm" id="chatbot-send-btn">
          <i data-feather="send" style="width: 16px; height: 16px;"></i>
        </button>
      </form>
      <div class="text-center mt-2">
        <small class="text-muted" id="chatbot-question-count">Questions: 0/5</small>
      </div>
    </div>
  </div>
</div>

<style>
  /* Chatbot Container */
  #gemini-chatbot-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1050;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  }

  /* Toggle Button */
  .chatbot-toggle-btn {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
    border: none;
    color: white;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    position: relative;
  }

  .chatbot-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(13, 110, 253, 0.5);
  }

  .chatbot-toggle-btn:active {
    transform: scale(0.95);
  }

  /* Chatbot Window */
  .chatbot-window {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 380px;
    max-width: calc(100vw - 40px);
    height: 600px;
    max-height: calc(100vh - 100px);
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s ease;
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Chatbot Header */
  .chatbot-header {
    padding: 16px;
    background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .chatbot-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
  }

  .chatbot-header h6 {
    color: white;
    font-size: 16px;
  }

  .chatbot-header small {
    color: rgba(255, 255, 255, 0.8);
    font-size: 12px;
  }

  .chatbot-header button {
    color: white;
  }

  .chatbot-header button:hover {
    opacity: 0.8;
  }

  /* Chatbot Body */
  .chatbot-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .chatbot-body::-webkit-scrollbar {
    width: 6px;
  }

  .chatbot-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
  }

  .chatbot-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
  }

  .chatbot-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }

  /* Chatbot Messages */
  .chatbot-message {
    display: flex;
    gap: 10px;
    animation: fadeIn 0.3s ease;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .chatbot-message-user {
    flex-direction: row-reverse;
  }

  .message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .chatbot-message-bot .message-avatar {
    background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
    color: white;
  }

  .chatbot-message-user .message-avatar {
    background: #e9ecef;
    color: var(--ghtk-primary);
  }

  .message-content {
    max-width: 75%;
    padding: 12px 16px;
    border-radius: 12px;
    word-wrap: break-word;
  }

  .chatbot-message-bot .message-content {
    background: white;
    color: #212529;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }

  .chatbot-message-user .message-content {
    background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
    color: white;
  }

  .message-content p {
    margin: 0;
    line-height: 1.5;
    font-size: 14px;
  }

  .message-content ul {
    font-size: 13px;
    line-height: 1.6;
  }

  /* Loading Indicator */
  .chatbot-loading {
    display: flex;
    gap: 4px;
    padding: 12px 16px;
  }

  .chatbot-loading span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
      background: var(--ghtk-primary);
    animation: bounce 1.4s infinite ease-in-out both;
  }

  .chatbot-loading span:nth-child(1) {
    animation-delay: -0.32s;
  }

  .chatbot-loading span:nth-child(2) {
    animation-delay: -0.16s;
  }

  @keyframes bounce {
    0%, 80%, 100% {
      transform: scale(0);
    }
    40% {
      transform: scale(1);
    }
  }

  /* Chatbot Footer */
  .chatbot-footer {
    padding: 16px;
    background: white;
    border-top: 1px solid #e9ecef;
  }

  .chatbot-footer form {
    margin: 0;
  }

  .chatbot-footer input {
    border-radius: 20px;
    border: 1px solid #e9ecef;
    padding: 8px 16px;
    font-size: 14px;
  }

  .chatbot-footer input:focus {
    border-color: var(--ghtk-primary);
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  }

  .chatbot-footer button {
    border-radius: 20px;
    padding: 8px 16px;
    min-width: 40px;
  }

  /* Responsive */
  @media (max-width: 576px) {
    .chatbot-window {
      width: calc(100vw - 20px);
      height: calc(100vh - 80px);
      bottom: 70px;
      right: 10px;
    }

    #gemini-chatbot-container {
      bottom: 10px;
      right: 10px;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather Icons
    if (typeof feather !== 'undefined') {
      feather.replace();
    }

    const chatbotToggleBtn = document.getElementById('chatbot-toggle-btn');
    const chatbotWindow = document.getElementById('chatbot-window');
    const chatbotCloseBtn = document.getElementById('chatbot-close-btn');
    const chatbotForm = document.getElementById('chatbot-form');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotSendBtn = document.getElementById('chatbot-send-btn');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const questionCountEl = document.getElementById('chatbot-question-count');

    let questionCount = 0;
    let chatHistory = [];
    let isProcessing = false;

    // Toggle chatbot window
    function toggleChatbot() {
      const isVisible = chatbotWindow.style.display !== 'none';
      chatbotWindow.style.display = isVisible ? 'none' : 'flex';
      
      if (!isVisible) {
        chatbotInput.focus();
        // Re-initialize Feather icons when window opens
        if (typeof feather !== 'undefined') {
          feather.replace();
        }
      }
    }

    chatbotToggleBtn.addEventListener('click', toggleChatbot);
    chatbotCloseBtn.addEventListener('click', toggleChatbot);

    // Reset chat history after 5 questions
    function resetChatHistory() {
      questionCount = 0;
      chatHistory = [];
      questionCountEl.textContent = 'Questions: 0/5';
      
      // Clear all messages except the welcome message
      const welcomeMessage = chatbotMessages.querySelector('.chatbot-message-bot');
      chatbotMessages.innerHTML = '';
      if (welcomeMessage) {
        chatbotMessages.appendChild(welcomeMessage);
      } else {
        // Re-add welcome message if it was removed
        const welcomeHtml = `
          <div class="chatbot-message chatbot-message-bot">
            <div class="message-avatar">
              <i data-feather="bot" style="width: 16px; height: 16px;"></i>
            </div>
            <div class="message-content">
              <p class="mb-0">Hello! I'm the AI assistant for the G14 Inventory system. I can help you with:</p>
              <ul class="mb-0 mt-2 ps-3">
                <li>Guide you on how to use the system</li>
                <li>Answer questions about data (based on your permissions)</li>
                <li>Answer questions about features</li>
              </ul>
              <p class="mb-0 mt-2">Please ask a question to get started!</p>
            </div>
          </div>
        `;
        chatbotMessages.innerHTML = welcomeHtml;
        if (typeof feather !== 'undefined') {
          feather.replace();
        }
      }
    }

    // Add message to chat
    function addMessage(content, isUser = false) {
      const messageDiv = document.createElement('div');
      messageDiv.className = `chatbot-message ${isUser ? 'chatbot-message-user' : 'chatbot-message-bot'}`;
      
      const avatarHtml = isUser 
        ? '<div class="message-avatar"><i data-feather="user" style="width: 16px; height: 16px;"></i></div>'
        : '<div class="message-avatar"><i data-feather="bot" style="width: 16px; height: 16px;"></i></div>';
      
      const contentHtml = `<div class="message-content"><p>${content.replace(/\n/g, '<br>')}</p></div>`;
      
      messageDiv.innerHTML = avatarHtml + contentHtml;
      chatbotMessages.appendChild(messageDiv);
      
      // Re-initialize Feather icons
      if (typeof feather !== 'undefined') {
        feather.replace();
      }
      
      // Scroll to bottom
      chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    // Show loading indicator
    function showLoading() {
      const loadingDiv = document.createElement('div');
      loadingDiv.className = 'chatbot-message chatbot-message-bot';
      loadingDiv.id = 'chatbot-loading';
      loadingDiv.innerHTML = `
        <div class="message-avatar">
          <i data-feather="bot" style="width: 16px; height: 16px;"></i>
        </div>
        <div class="message-content chatbot-loading">
          <span></span>
          <span></span>
          <span></span>
        </div>
      `;
      chatbotMessages.appendChild(loadingDiv);
      
      if (typeof feather !== 'undefined') {
        feather.replace();
      }
      
      chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    // Remove loading indicator
    function removeLoading() {
      const loading = document.getElementById('chatbot-loading');
      if (loading) {
        loading.remove();
      }
    }

    // Handle form submission
    chatbotForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      if (isProcessing) return;
      
      const message = chatbotInput.value.trim();
      if (!message) return;

      // Add user message
      addMessage(message, true);
      chatbotInput.value = '';
      
      // Update question count
      questionCount++;
      questionCountEl.textContent = `Questions: ${questionCount}/5`;
      
      // Add to chat history
      chatHistory.push({
        role: 'user',
        content: message
      });

      // Check if we need to reset (after 5 questions, reset on 6th)
      if (questionCount > 5) {
        resetChatHistory();
        addMessage('Chat history has been reset. You can continue asking new questions!', false);
        return;
      }

      // Show loading
      showLoading();
      isProcessing = true;
      chatbotSendBtn.disabled = true;

      try {
        const response = await fetch('{{ route("chat.gemini") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            message: message,
            chat_history: chatHistory.slice(-10) // Only send last 10 messages for context
          })
        });

        const data = await response.json();
        removeLoading();

        if (data.success) {
          addMessage(data.answer, false);
          
          // Add bot response to chat history
          chatHistory.push({
            role: 'assistant',
            content: data.answer
          });
        } else {
          addMessage(data.message || 'Sorry, an error occurred. Please try again later.', false);
        }
      } catch (error) {
        removeLoading();
        addMessage('Sorry, a connection error occurred. Please check your network connection and try again.', false);
        console.error('Chatbot error:', error);
      } finally {
        isProcessing = false;
        chatbotSendBtn.disabled = false;
        chatbotInput.focus();
      }
    });

    // Allow Enter key to submit (but Shift+Enter for new line)
    chatbotInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        chatbotForm.dispatchEvent(new Event('submit'));
      }
    });
  });
</script>

