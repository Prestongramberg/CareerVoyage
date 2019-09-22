export default function getInitialUiState( unreadMessages ) {
    return {
        isChatLoading: false,
        isChatOpen: false,
        isSearchOpen: false,
        isThreadLoading: false,
        isThreadOpen: false,
        unreadMessages: unreadMessages,
    }
}
