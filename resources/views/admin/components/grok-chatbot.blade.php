<!-- Grok Chatbot Component -->
<div id="grok-chatbot-container">
  <!-- Chatbot Toggle Button -->
  <button
    id="grok-chatbot-toggle"
    class="btn btn-primary rounded-circle shadow-lg"
    style="
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 60px;
      height: 60px;
      z-index: 1050;
      display: flex;
      align-items: center;
      justify-content: center;
      border: none;
      box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
      transition: all 0.3s ease;
    "
    title="Open Chatbot"
  >
    <i data-feather="message-circle" style="width: 24px; height: 24px;"></i>
  </button>

  <!-- Chatbot Panel -->
  <div
    id="grok-chatbot-panel"
    class="shadow-lg"
    style="
      position: fixed;
      bottom: 90px;
      right: 20px;
      width: 400px;
      max-width: calc(100vw - 40px);
      height: 600px;
      max-height: calc(100vh - 120px);
      background: white;
      border-radius: 16px;
      z-index: 1051;
      display: none;
      flex-direction: column;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
      overflow: hidden;
    "
  >
    <!-- Chatbot Header -->
    <div
      class="d-flex align-items-center justify-content-between p-3"
      style="
        background: linear-gradient(135deg, #0d6efd 0%, #17a2b8 100%);
        color: white;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      "
    >
      <div class="d-flex align-items-center">
        <i data-feather="message-circle" style="width: 20px; height: 20px; margin-right: 10px;"></i>
        <h6 class="mb-0 fw-semibold">Grok Assistant</h6>
      </div>
      <div class="d-flex align-items-center">
        <span
          id="question-counter"
          class="badge bg-light text-dark me-2"
          style="font-size: 0.75rem;"
        >
          0/5
        </span>
        <button
          id="grok-chatbot-close"
          class="btn btn-sm text-white"
          style="padding: 0; border: none; background: transparent;"
          title="Close Chatbot"
        >
          <i data-feather="x" style="width: 20px; height: 20px;"></i>
        </button>
      </div>
    </div>

    <!-- Chat Messages Area -->
    <div
      id="grok-chat-messages"
      class="flex-grow-1 p-3"
      style="
        overflow-y: auto;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
        gap: 12px;
      "
    >
      <!-- Welcome Message -->
      <div
        id="welcome-message"
        class="alert alert-info mb-0"
        style="border-radius: 12px; border: none; background: linear-gradient(135deg, rgba(23, 162, 184, 0.1) 0%, rgba(13, 110, 253, 0.1) 100%);"
      >
        <div class="d-flex align-items-start">
          <i data-feather="info" style="width: 18px; height: 18px; margin-right: 8px; margin-top: 2px;"></i>
          <div>
            <strong>Welcome!</strong>
            <p class="mb-0 mt-1" style="font-size: 0.875rem;">
              I'm your virtual assistant. I can help you navigate the system, provide information about products, and answer questions. You can ask up to 5 questions per session.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Chat Input Area -->
    <div
      class="p-3"
      style="
        background: white;
        border-top: 1px solid #e9ecef;
      "
    >
      <form id="grok-chat-form" class="d-flex gap-2">
        @csrf
        <input
          type="text"
          id="grok-chat-input"
          class="form-control"
          placeholder="Type your message..."
          autocomplete="off"
          style="border-radius: 12px; border: 1px solid #dee2e6;"
        />
        <button
          type="submit"
          id="grok-chat-send"
          class="btn btn-primary"
          style="border-radius: 12px; min-width: 50px;"
          title="Send Message"
        >
          <i data-feather="send" style="width: 18px; height: 18px;"></i>
        </button>
      </form>
      <div
        id="grok-chat-loading"
        class="text-center mt-2"
        style="display: none;"
      >
        <div class="spinner-border spinner-border-sm text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <span class="ms-2" style="font-size: 0.875rem; color: #6c757d;">Thinking...</span>
      </div>
    </div>
  </div>
</div>

<style>
  /* Chatbot Message Styles */
  .grok-message {
    display: flex;
    margin-bottom: 12px;
    animation: fadeIn 0.3s ease;
  }

  .grok-message.user {
    justify-content: flex-end;
  }

  .grok-message.assistant {
    justify-content: flex-start;
  }

  .grok-message-content {
    max-width: 75%;
    padding: 12px 16px;
    border-radius: 12px;
    word-wrap: break-word;
    line-height: 1.5;
  }

  .grok-message.user .grok-message-content {
    background: linear-gradient(135deg, #0d6efd 0%, #17a2b8 100%);
    color: white;
    border-bottom-right-radius: 4px;
  }

  .grok-message.assistant .grok-message-content {
    background: white;
    color: #212529;
    border: 1px solid #e9ecef;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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

  /* Scrollbar for chat messages */
  #grok-chat-messages::-webkit-scrollbar {
    width: 6px;
  }

  #grok-chat-messages::-webkit-scrollbar-track {
    background: transparent;
  }

  #grok-chat-messages::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 10px;
  }

  #grok-chat-messages::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
  }

  /* Responsive */
  @media (max-width: 576px) {
    #grok-chatbot-panel {
      width: calc(100vw - 20px);
      right: 10px;
      bottom: 80px;
    }

    #grok-chatbot-toggle {
      right: 10px;
      bottom: 10px;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather Icons
    if (typeof feather !== 'undefined') {
      feather.replace();
    }

    const toggleBtn = document.getElementById('grok-chatbot-toggle');
    const closeBtn = document.getElementById('grok-chatbot-close');
    const panel = document.getElementById('grok-chatbot-panel');
    const form = document.getElementById('grok-chat-form');
    const input = document.getElementById('grok-chat-input');
    const messagesContainer = document.getElementById('grok-chat-messages');
    const loadingIndicator = document.getElementById('grok-chat-loading');
    const questionCounter = document.getElementById('question-counter');
    const welcomeMessage = document.getElementById('welcome-message');

    // Get user ID for localStorage key
    const userId = {{ auth()->id() }};
    const storageKey = `grok_chatbot_conversation_${userId}`;

    // Load conversation from localStorage
    let conversation = loadConversation();
    let isProcessing = false;

    // Load conversation from localStorage
    function loadConversation() {
      try {
        const stored = localStorage.getItem(storageKey);
        if (stored) {
          const parsed = JSON.parse(stored);
          // Only load if it's an array and has valid structure
          if (Array.isArray(parsed)) {
            return parsed;
          }
        }
      } catch (e) {
        console.error('Error loading conversation from localStorage:', e);
      }
      return [];
    }

    // Save conversation to localStorage
    function saveConversation(conv) {
      try {
        localStorage.setItem(storageKey, JSON.stringify(conv));
      } catch (e) {
        console.error('Error saving conversation to localStorage:', e);
      }
    }

    // Clear conversation from localStorage
    function clearStoredConversation() {
      try {
        localStorage.removeItem(storageKey);
      } catch (e) {
        console.error('Error clearing conversation from localStorage:', e);
      }
    }

    // Restore conversation UI on page load
    function restoreConversationUI() {
      if (conversation.length > 0) {
        // Hide welcome message
        if (welcomeMessage) {
          welcomeMessage.style.display = 'none';
        }
        
        // Restore all messages
        conversation.forEach(function(item) {
          if (item.user) {
            addMessage('user', item.user, false); // false = don't scroll yet
          }
          if (item.assistant) {
            addMessage('assistant', item.assistant, false);
          }
        });
        
        // Update question counter
        questionCounter.textContent = `${conversation.length}/5`;
        
        // Scroll to bottom after restoring
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
      }
    }

    // Restore conversation when page loads
    restoreConversationUI();

    // Toggle panel
    toggleBtn.addEventListener('click', function() {
      panel.style.display = panel.style.display === 'flex' ? 'none' : 'flex';
      if (panel.style.display === 'flex') {
        input.focus();
        // Re-initialize Feather icons after showing panel
        if (typeof feather !== 'undefined') {
          feather.replace();
        }
      }
    });

    closeBtn.addEventListener('click', function() {
      panel.style.display = 'none';
    });

    // Handle form submission
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      if (isProcessing) return;

      const message = input.value.trim();
      if (!message) return;

      // Check if this is the 6th question (conversation has 5 items)
      if (conversation.length >= 5) {
        // Clear conversation before processing
        clearConversation();
      }

      // Add user message to UI
      addMessage('user', message);
      input.value = '';
      
      // Hide welcome message after first question
      if (welcomeMessage && conversation.length === 0) {
        welcomeMessage.style.display = 'none';
      }

      // Show loading
      loadingIndicator.style.display = 'block';
      isProcessing = true;
      updateSendButton(false);

      try {
        const response = await fetch('{{ route("grok.chat") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
          },
          body: JSON.stringify({
            message: message,
            conversation: conversation,
          }),
        });

        const data = await response.json();

        if (data.error) {
          addMessage('assistant', data.response || 'Sorry, an error occurred. Please try again.');
        } else {
          addMessage('assistant', data.response);
          conversation = data.conversation || [];
          
          // Save conversation to localStorage
          saveConversation(conversation);
          
          // Update question counter
          const questionCount = data.questionCount || conversation.length;
          questionCounter.textContent = `${questionCount}/5`;
          
          // If this was the 5th question, show notification that next will clear
          if (data.shouldClear) {
            // Show a subtle notification
            const notification = document.createElement('div');
            notification.className = 'alert alert-warning mb-2';
            notification.style.cssText = 'border-radius: 8px; padding: 8px 12px; font-size: 0.875rem; margin-bottom: 8px;';
            notification.innerHTML = '<i data-feather="info" style="width: 14px; height: 14px; margin-right: 6px;"></i> You have reached the 5-question limit. The conversation will be cleared on your next question.';
            messagesContainer.appendChild(notification);
            
            // Re-initialize Feather icons
            if (typeof feather !== 'undefined') {
              feather.replace();
            }
            
            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
          }
        }
      } catch (error) {
        console.error('Chat error:', error);
        addMessage('assistant', 'Sorry, I encountered an error. Please try again later.');
      } finally {
        loadingIndicator.style.display = 'none';
        isProcessing = false;
        updateSendButton(true);
        input.focus();
      }
    });

    // Add message to chat
    function addMessage(role, content, shouldScroll = true) {
      const messageDiv = document.createElement('div');
      messageDiv.className = `grok-message ${role}`;
      
      const contentDiv = document.createElement('div');
      contentDiv.className = 'grok-message-content';
      contentDiv.textContent = content;
      
      messageDiv.appendChild(contentDiv);
      messagesContainer.appendChild(messageDiv);
      
      // Scroll to bottom only if shouldScroll is true
      if (shouldScroll) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
      }
      
      // Re-initialize Feather icons
      if (typeof feather !== 'undefined') {
        feather.replace();
      }
    }

    // Clear conversation
    function clearConversation() {
      conversation = [];
      messagesContainer.innerHTML = '';
      if (welcomeMessage) {
        welcomeMessage.style.display = 'block';
      }
      questionCounter.textContent = '0/5';
      // Clear from localStorage
      clearStoredConversation();
    }

    // Update send button state
    function updateSendButton(enabled) {
      const sendBtn = document.getElementById('grok-chat-send');
      if (sendBtn) {
        sendBtn.disabled = !enabled;
        sendBtn.style.opacity = enabled ? '1' : '0.6';
      }
    }

    // Auto-resize input on Enter (but allow Shift+Enter for new line)
    input.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        form.dispatchEvent(new Event('submit'));
      }
    });
  });
</script>
