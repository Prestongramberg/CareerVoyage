import * as actionTypes from "../actions/actionTypes";

export default (state = {}, action) => {

    switch (action.type) {

        case actionTypes.SEARCH_CHATTABLE_USERS_SUCCESS:

            debugger;
            return {
                ...state,
                roles: get_roles(action.users.all),
                companies: get_companies(action.users.all),
                interests: get_interests(action.users.all),
                company_admins: get_company_admins(action.users.all),
                schools: get_schools(action.users.all),
                courses_taught: get_courses_taught(action.users.all),
                primary_industries: get_primary_industries(action.users.all),
                secondary_industries: get_secondary_industries(action.users.all)
            };
        case actionTypes.NOTIFICATIONS_SENDING_SUCCESS:
            return state;

        default:
            return state;
    }
};

function get_roles( users ) {

    const filters = [];

    users.forEach(user => {
        if ( user.roles && Object.keys(user.roles).length > 0) {
            for(const property in user.roles) {

                if(!property) {
                    continue;
                }

                let role = user.roles[property];

                if(!role) {
                    continue;
                }

                let exists = filters.some(function(el) {
                    return el.value === role['role_id'];
                });

                if(!exists) {
                    filters.push({label: role['role_name'], value: role['role_id']});
                }
            }
        }
    });

    return filters;
}

function get_companies( users ) {

    const filters = [];

    users.forEach(user => {

        if ( user.company_id && user.company_name) {

            let exists = filters.some(function(el) {
                return el.value === user.company_id;
            });

            if(!exists) {
                filters.push({label: user.company_name, value: user.company_id});
            }
        }
    });

    return filters;
}

function get_interests( users ) {

    const filters = [];

    users.forEach(user => {

        if ( user.interests) {

            filters.push(user.interests);
        }
    });

    return filters;
}

function get_company_admins( users ) {

    const filters = [];

    users.forEach(user => {

        if ( user.company_administrator) {

            let exists = filters.some(function(el) {
                return el.value === user.company_administrator;
            });

            if(!exists) {
                filters.push({label: user.company_administrator, value: user.company_administrator});
            }
        }
    });

    return filters;
}

function get_schools( users ) {

    const filters = [];

    users.forEach(user => {

        if(user.school_name && user.school_id) {

            let exists = filters.some(function(el) {
                return el.value === user.school_id;
            });

            if(!exists) {
                filters.push({label: user.school_name, value: user.school_id});
            }
        }

        if(user.schools) {

            for (const property in user.schools) {

                let school = user.schools[property];

                let exists = filters.some(function(el) {
                    return el.value === school.school_id;
                });

                if(!exists) {
                    filters.push({label: school.school_name, value: school.school_id});
                }

            }
        }

    });

    return filters;
}

function get_courses_taught( users ) {

    const filters = [];

    users.forEach(user => {

        if ( user.courses) {

            for (const property in user.courses) {

                if(property === "") {
                    continue;
                }

                let course = user.courses[property];

                let exists = filters.some(function(el) {
                    return el.value === course.course_id;
                });

                if(!exists) {
                    filters.push({label: course.course_title, value: course.course_id});
                }

            }
        }
    });

    return filters;
}

function get_primary_industries( users ) {

    const filters = [];

    users.forEach(user => {

        if ( user.secondary_industries ) {

            for (const property in user.secondary_industries) {

                let industryData = user.secondary_industries[property];
                let primaryIndustryId = industryData.primary_industry_id;
                let primaryIndustryName = industryData.primary_industry_name;

                if(!primaryIndustryId) {
                    continue;
                }

                let exists = filters.some(function(el) {
                    return el.value === primaryIndustryId;
                });

                if(!exists) {
                    filters.push({label: primaryIndustryName, value: primaryIndustryId});
                }

            }
        }
    });

    return filters;
}

function get_secondary_industries( users ) {

    const filters = [];

    users.forEach(user => {

        if ( user.secondary_industries ) {

            for (const property in user.secondary_industries) {

                let industryData = user.secondary_industries[property];
                let primaryIndustryId = industryData.primary_industry_id;
                let primaryIndustryName = industryData.primary_industry_name;
                let secondaryIndustryId = industryData.secondary_industry_id;
                let secondaryIndustryName = industryData.secondary_industry_name;

                if(!secondaryIndustryId) {
                    continue;
                }

                let exists = filters.some(function(el) {
                    return el.value === secondaryIndustryId;
                });

                if(!exists) {
                    filters.push({secondaryIndustryName: secondaryIndustryName, secondaryIndustryId: secondaryIndustryId, primaryIndustryId: primaryIndustryId });
                }

            }
        }
    });

    return filters;
}