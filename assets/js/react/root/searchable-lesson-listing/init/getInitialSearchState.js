export default function getInitialSearchState() {
    return {
        industry: 0,
        query: '',
        loading: true,
        loadingLessons: true,
        loadingUser: true,
        educator: false,
        presenter: false
    }
}