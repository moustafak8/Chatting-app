const BASE_URL = "http://localhost/Chatting-app/chatting-server/";
const userId = parseInt(localStorage.getItem("id")); // User 1 or User 2
const conversationId = 1;
const msgInput = document.getElementById("msgInput");
const sendBtn = document.getElementById("sendBtn");
const messagesContainer = document.getElementById("messages");
const refreshBtn = document.getElementById("refreshBtn");

const otherUserId = userId === 1 ? 2 : 1;

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
function getStatusIcon(deliveredAt, readAt) {
  if (readAt) {
    return "✓✓"; // Double tick (read) - will be colored blue
  } else if (deliveredAt) {
    return "✓✓"; // Double tick (delivered, not read yet) - gray
  } else {
    return "✓"; // Single tick (sent) - gray
  }
}

function formatDateTime(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleString([], {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function getStatusTooltip(deliveredAt, readAt) {
  let tooltip = "";
  if (deliveredAt) {
    tooltip = `Delivered at ${formatDateTime(deliveredAt)}`;
  }
  if (readAt) {
    if (tooltip) tooltip += "\n";
    tooltip += `Read at ${formatDateTime(readAt)}`;
  }
  return tooltip || "Sent";
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
    const isSent = msg.sender_user_id == userId;
    messageDiv.className = `message ${isSent ? "sent" : "received"}`;

    let statusIcon = "";
    let statusClass = "";
    let tooltip = "";
    if (isSent) {
      statusIcon = getStatusIcon(msg.delivered_at, msg.read_at);
      tooltip = getStatusTooltip(msg.delivered_at, msg.read_at);
      // Add class for read status (colored double tick)
      if (msg.read_at) {
        statusClass = "read";
      } else if (msg.delivered_at) {
        statusClass = "delivered";
      } else {
        statusClass = "sent";
      }
    }

    messageDiv.innerHTML = `
      <div class="message-content">
        <p>${escapeHtml(msg.content)}</p>
        <div class="message-meta">
          <span class="message-time">${formatTime(msg.created_at)}</span>
          ${
            isSent
              ? `<span class="message-status ${statusClass}" title="${escapeHtml(
                  tooltip
                )}">${statusIcon}</span>`
              : ""
          }
        </div>
      </div>
    `;

    messagesContainer.appendChild(messageDiv);
  });

  messagesContainer.scrollTop = messagesContainer.scrollHeight;
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

function sendMessage() {
  const message = msgInput.value.trim();
  if (!message) {
    console.error("Message cannot be empty");
    return;
  }

  const originalMessage = message;
  const tempMessageId = "temp_" + Date.now();
  const optimisticMessage = {
    message_id: tempMessageId,
    conversation_id: conversationId,
    sender_user_id: userId,
    content: message,
    created_at: new Date().toISOString(),
    delivered_at: null,
    read_at: null,
  };

  const messageDiv = document.createElement("div");
  messageDiv.className = "message sent";
  messageDiv.id = tempMessageId;
  messageDiv.innerHTML = `
    <div class="message-content">
      <p>${escapeHtml(message)}</p>
      <div class="message-meta">
        <span class="message-time">${formatTime(
          optimisticMessage.created_at
        )}</span>
        <span class="message-status">✓</span>
      </div>
    </div>
  `;
  messagesContainer.appendChild(messageDiv);
  messagesContainer.scrollTop = messagesContainer.scrollHeight;
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
        const statusData = {
          message_id: messageId,
          user_id: otherUserId,
        };

        return axios
          .post(BASE_URL + "status/create", statusData)
          .then((statusResponse) => {
            if (statusResponse && statusResponse.data.status === 200) {
              console.log("Status created successfully for user", otherUserId);
            }
            return fetchMessages();
          });
      } else {
        throw new Error("Failed to send message");
      }
    })
    .catch((error) => {
      console.error("Error sending message:", error);
      const tempMsg = document.getElementById(tempMessageId);
      if (tempMsg) {
        tempMsg.remove();
      }
      alert("Failed to send message. Please try again.");
      msgInput.value = originalMessage;
    });
}

sendBtn.addEventListener("click", sendMessage);

msgInput.addEventListener("keypress", function (e) {
  if (e.key === "Enter") {
    sendMessage();
  }
});
if (refreshBtn) {
  refreshBtn.addEventListener("click", function () {
    markMessagesAsRead();
  });
}

function markMessagesAsRead() {
  setTimeout(() => {
    if (document.hasFocus()) {
      axios
        .post(BASE_URL + "message/mark-read", {
          conversation_id: conversationId,
          user_id: userId,
        })
        .then((response) => {
          if (response.data.status === 200) {
            console.log("Messages marked as read");
            fetchMessages();
          }
        })
        .catch((error) => {
          console.error("Error marking messages as read:", error);
        });
    }
  }, 1000); // 1 second delay
}

function createSummary() {
  let summaryContainer = document.getElementById("unread-summary-container");
  if (!summaryContainer) {
    summaryContainer = document.createElement("div");
    summaryContainer.id = "unread-summary-container";
    const chatContainer = document.querySelector(".chat-container");
    if (chatContainer) {
      chatContainer.insertBefore(
        summaryContainer,
        document.getElementById("messages")
      );
    }
  }
  return summaryContainer;
}

function fetchUnreadSummary() {
  const data = {
    conversation_id: conversationId,
    user_id: userId,
  };
  axios
    .post(BASE_URL + "message/unread-summary", data)
    .then((response) => {
      if (
        response.data.status === 200 &&
        response.data.data &&
        response.data.data.summary
      ) {
        const summaryText = response.data.data.summary;
        if (
          summaryText !== "No unread messages" &&
          summaryText !== "Less than 3 unread messages, no summary generated"
        ) {
          const summaryContainer = createSummary();
          summaryContainer.textContent =
            "Unread Messages Summary: " + summaryText;
        } else {
          const summaryContainer = document.getElementById(
            "unread-summary-container"
          );
          if (summaryContainer) summaryContainer.textContent = "";
        }
      } else {
        console.error("Failed to fetch unread messages summary");
      }
    })
    .catch((error) => {
      console.error("Error fetching unread summary:", error);
    });
}

document.addEventListener("DOMContentLoaded", function () {
  fetchMessages().then(() => {
    fetchUnreadSummary();
  });
  markMessagesAsRead();
});
