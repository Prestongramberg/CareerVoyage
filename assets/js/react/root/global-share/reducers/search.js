import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {

    switch (action.type) {
        case actionTypes.QUERY_BY_ROLE:

            return {
                ...state,
                roles: action.roles
            };
            case actionTypes.QUERY_BY_USER_ROLE:

                return {
                    ...state,
                    user_roles: action.user_roles
                };

        case actionTypes.QUERY_BY_COMPANY:

            return {
                ...state,
                companies: action.companies
            };

        case actionTypes.QUERY_BY_INTERESTS:

            return {
                ...state,
                interests: action.interests
            };

        case actionTypes.QUERY_BY_COMPANY_ADMINISTRATORS:

            return {
                ...state,
                company_admins: action.company_admins
            };

        case actionTypes.QUERY_BY_COURSE_TAUGHT:

            return {
                ...state,
                courses_taught: action.courses_taught
            };
        case actionTypes.QUERY_BY_SCHOOL:

            return {
                ...state,
                schools: action.schools
            };

        case actionTypes.QUERY_BY_PRIMARY_INDUSTRY:

            return {
                ...state,
                primary_industries: action.primary_industries
            };

        case actionTypes.QUERY_BY_SECONDARY_INDUSTRY:

            return {
                ...state,
                secondary_industries: action.secondary_industries
            };

        case actionTypes.SEARCH_CHATTABLE_USERS:
            return {
                ...state,
                query: action.searchQuery
            };
        default:
            return state;
    }
};
