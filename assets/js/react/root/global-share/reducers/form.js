import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {

    switch (action.type) {

        case actionTypes.FILTER_CHANGE_REQUESTED:

            if(action.context.fieldName === 'userRole') {

                debugger;
                state = removeByKey(state, 'company');
                state = removeByKey(state, 'rolesWillingToFulfill');
                state = removeByKey(state, 'primaryIndustry');
                state = removeByKey(state, 'secondaryIndustries');
                state = removeByKey(state, 'school');
                state = removeByKey(state, 'myCourses');
            }

            if (!action.context.value) {
                return removeByKey(state, action.context.fieldName);
            }

            return {
                ...state,
                [action.context.fieldName]: action.context.value
            };

        case actionTypes.FILTER_CHANGE_FAILURE:


            return removeByKey(state, action.context.fieldName);

        default:
            return state;
    }
};

function removeByKey(myObj, deleteKey) {
    return Object.keys(myObj)
        .filter(key => key !== deleteKey)
        .reduce((result, current) => {
            result[current] = myObj[current];
            return result;
        }, {});
}