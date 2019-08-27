// on page load - show chat in the bottom right window with "bubble" of number of unseen messages - passed in by TWIG

export const CHAT_LOAD = "CHAT_LOAD";
export const CHAT_LOAD_SUCCESS = "CHAT_LOAD_SUCCESS";
export const CHAT_LOAD_FAILURE = "CHAT_LOAD_FAILURE";
// when user clicks chat window on screen
// call "UNDEFINED" endpoint with userId to get all users that have been previously chatted with

export const CHAT_CLOSE_WINDOW = "CHAT_CLOSE_WINDOW";
// closes out chat state completely, killing any state

export const CHAT_LOAD_THREAD = "CHAT_LOAD_THREAD";
export const CHAT_LOAD_THREAD_SUCESS = "CHAT_LOAD_THREAD_SUCESS";
export const CHAT_LOAD_THREAD_FAILURE = "CHAT_LOAD_THREAD_FAILURE";
// when user clicks on a chat thread
// call "create_single_chat" endpoint with userId to create a new chat stream, or return an existing one

export const CHAT_SEND_MESSAGE = "CHAT_SEND_MESSAGE";
export const CHAT_SEND_MESSAGE_SUCCESS = "CHAT_SEND_MESSAGE_SUCCESS";
export const CHAT_SEND_MESSAGE_FAILURE = "CHAT_SEND_MESSAGE_FAILURE";
// when user sends a message
// call "message_chat" endpoint (/chats/{id}/message) with guid of thread and POST message

// Events
// Subscribe to global chat-${userId} with Payload information for incoming messages?
// This will also "tick" up the "bubble" amount unless the user is already in that chat thread
