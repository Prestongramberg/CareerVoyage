import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function updateAllVideoSearchQuery(query) {
    return {
        type: actionTypes.ALL_VIDEO_SEARCH_QUERY_CHANGED,
        query: query
    };
}

export function updateAllVideoIndustryQuery(industry) {
    return {
        type: actionTypes.ALL_VIDEO_INDUSTRY_QUERY_CHANGED,
        industry: industry
    };
}

export function updateCompanyVideoSearchQuery(query) {
    return {
        type: actionTypes.COMPANY_VIDEO_SEARCH_QUERY_CHANGED,
        query: query
    };
}

export function updateCompanyVideoIndustryQuery(industry) {
    return {
        type: actionTypes.COMPANY_VIDEO_INDUSTRY_QUERY_CHANGED,
        industry: industry
    };
}

export function updateCareerVideoSearchQuery(query) {
    return {
        type: actionTypes.CAREER_VIDEO_SEARCH_QUERY_CHANGED,
        query: query
    };
}

export function updateCareerVideoIndustryQuery(industry) {
    return {
        type: actionTypes.CAREER_VIDEO_INDUSTRY_QUERY_CHANGED,
        industry: industry
    };
}

export function updateProfessionalVideoSearchQuery(query) {
    return {
        type: actionTypes.PROFESSIONAL_VIDEO_SEARCH_QUERY_CHANGED,
        query: query
    };
}

export function updateProfessionalVideoIndustryQuery(industry) {

    debugger;
    return {
        type: actionTypes.PROFESSIONAL_VIDEO_INDUSTRY_QUERY_CHANGED,
        industry: industry
    };
}

export function updateCompanyQuery(company) {
    return {
        type: actionTypes.COMPANY_QUERY_CHANGED,
        company: company
    };
}

export function loadVideos(url) {

    return (dispatch, getState) => {

        dispatch({type: actionTypes.VIDEOS_LOADING})

        return api.get(url)
            .then((response) => {

                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.VIDEOS_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.VIDEOS_LOADING_FAILURE,
                        error: "Something went wrong, please try refreshing the page."
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.VIDEOS_LOADING_FAILURE,
                error: "Something went wrong, please try refreshing the page."
            }))
    }
}

export function loadUser(url) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.USER_LOADING})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.USER_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                    dispatch({
                        type: actionTypes.USER_LOADING_FAILURE
                    })

                }
            })
            .catch(()=> dispatch({
                type: actionTypes.USER_LOADING_FAILURE
            }))
    }
}

export function favoriteVideo(url, videoId) {

    return (dispatch, getState) => {

        return api.post(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {

                    dispatch({type: actionTypes.VIDEO_FAVORITE, response: response.responseBody, videoId: videoId})

                }  else {
                    // do nothing
                }
            })
            .catch(()=> {
                // do nothing
            });
    }
}

export function unfavoriteVideo(url, videoId) {

    return (dispatch, getState) => {

        return api.post(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {

                    dispatch({type: actionTypes.VIDEO_UNFAVORITE, response: response.responseBody, videoId: videoId})

                }  else {
                    // do nothing
                }
            })
            .catch(()=> {
                // do nothing
            });
    }
}
