export default function getInitialChatState( unreadMessages ) {
    return {
        currentMessage: "",
        chatId: 0,
        foundUsersInSearch: [],
        messages: [],
        searchTerm: "",
        unreadMessages: unreadMessages,
        usersHistory: [],
        userEngagedWith: {},
    }
}
