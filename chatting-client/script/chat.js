const BASE_URL = "http://localhost/Chatting-app/chatting-server/";
const userId = localStorage.getItem("id");
const conversationId = 1;
const msgInput = document.getElementById("msgInput");
const sendBtn = document.getElementById("sendBtn");
const messagesContainer = document.getElementById("messages");
const refreshBtn = document.getElementById("refreshBtn");
let otherParticipantId = null;
function loadParticipants() {
  return axios.get(BASE_URL + "participant").then((response) => {
    if (response.data.status === 200) {
      const participants = response.data.data;
      const otherParticipant = participants.find(
        (p) => p.user_id != userId && p.conversation_id == conversationId
      );
      if (otherParticipant) {
        otherParticipantId = otherParticipant.user_id;
      }
    }
  });
}
function fetchMessages() {
  return axios
    .get(BASE_URL + "message", {
      params: {
        conversation_id: conversationId,
        user_id: userId,
      },
    })
    .then((response) => {
      if (response.data.status === 200) {
        displayMessages(response.data.data);
        return response.data.data;
      } else {
        throw new Error("Failed to fetch messages");
      }
    })
    .catch((error) => {
      console.error("Error fetching messages:", error);
      messagesContainer.innerHTML =
        "<p>Error loading messages. Please try again.</p>";
    });
}

function displayMessages(messages) {
  messagesContainer.innerHTML = "";

  if (messages.length === 0) {
    messagesContainer.innerHTML =
      "<p>No messages yet. Start the conversation!</p>";
    return;
  }

  messages.forEach((msg) => {
    const messageDiv = document.createElement("div");
    messageDiv.className = `message ${
      msg.sender_user_id == userId ? "sent" : "received"
    }`;

    const isSent = msg.sender_user_id == userId;
    let statusIcon = "";

    if (isSent) {
      if (msg.read_at) {
        statusIcon = "✓✓";
      } else if (msg.delivered_at) {
        statusIcon = "✓✓";
      } else {
        statusIcon = "✓";
      }
    }

    messageDiv.innerHTML = `
      <div class="message-content">
        <p>${escapeHtml(msg.content)}</p>
        <div class="message-meta">
          <span class="message-time">${formatTime(msg.created_at)}</span>
          ${isSent ? `<span class="message-status">${statusIcon}</span>` : ""}
        </div>
      </div>
    `;

    messagesContainer.appendChild(messageDiv);
  });

  messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function formatTime(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}
document.addEventListener("DOMContentLoaded", function () {
  loadParticipants().then(() => {
    fetchMessages();
  });
});

if (refreshBtn) {
  refreshBtn.addEventListener("click", function () {
    fetchMessages();
  });
}

sendBtn.addEventListener("click", function () {
  const message = msgInput.value.trim();

  if (!message) {
    console.error("Message cannot be empty");
    return;
  }

  msgInput.value = "";

  const messageData = {
    conversation_id: conversationId,
    sender_user_id: userId,
    content: message,
  };

  axios
    .post(BASE_URL + "message/create", messageData)
    .then((response) => {
      if (response.data.status === 200) {
        const messageId = response.data.data.id;
        console.log("Message sent successfully:", messageId);

        // Create status for the other participant
        if (otherParticipantId) {
          const statusData = {
            message_id: messageId,
            user_id: otherParticipantId,
          };

          return axios.post(BASE_URL + "status/create", statusData);
        } else {
          console.warn("Other participant ID not found");
          return loadParticipants().then(() => {
            if (otherParticipantId) {
              const statusData = {
                message_id: messageId,
                user_id: otherParticipantId,
              };
              return axios.post(BASE_URL + "status/create", statusData);
            }
          });
        }
      } else {
        throw new Error("Failed to send message");
      }
    })
    .then((statusResponse) => {
      if (statusResponse && statusResponse.data.status === 200) {
        console.log("Status created successfully");

        fetchMessages();
      } else {
        fetchMessages();
      }
    })
    .catch((error) => {
      console.error("Error sending message:", error);
      msgInput.value = message;
    });
});
