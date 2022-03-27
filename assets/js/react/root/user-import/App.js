import React, {useState, useEffect, useRef, useCallback} from "react"
import PropTypes from "prop-types";
import * as api from "../../utilities/api/api";
import StudentForm from "./components/StudentForm";
import EducatorForm from "./components/EducatorForm";

export default function App(props) {

    useEffect(() => {
        // Some initialization logic here
        loadUserImport();
    }, []);

    debugger;

    const [userImportUsers, setUserImportUsers] = useState([]);
    const [userImport, setUserImport] = useState({});

    const loadUserImport = () => {

        debugger;
        let url = window.Routing.generate("user_import_get", {
            uuid: props.userImportUuid
        });

        api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    debugger;
                    let items = response.responseBody.userImport.userImportUsers;
                    setUserImportUsers(items);
                    setUserImport(response.responseBody.userImport);
                }
            })
            .catch((e) => {
            })

    }

    const onChangeFirstName = (event) => {
        let userImportUserId = event.target.getAttribute("data-id");
        let firstName = event.target.value;

        setUserImportUsers(prevItems => prevItems.map((item, index) => {
            return item.id != userImportUserId ? item : { ...item, firstName: firstName }
        }))
    }

    const onChangeLastName = (event) => {
        let userImportUserId = event.target.getAttribute("data-id");
        let lastName = event.target.value;

        setUserImportUsers(prevItems => prevItems.map((item, index) => {
            return item.id != userImportUserId ? item : { ...item, lastName: lastName }
        }))
    }

    const onChangeGraduatingYear = (event) => {
        let userImportUserId = event.target.getAttribute("data-id");
        let graduatingYear = event.target.value;

        setUserImportUsers(prevItems => prevItems.map((item, index) => {
            return item.id != userImportUserId ? item : { ...item, graduatingYear: graduatingYear }
        }))
    }

    const onChangeEducatorEmail = (event) => {
        let userImportUserId = event.target.getAttribute("data-id");
        let educatorEmail = event.target.value;

        setUserImportUsers(prevItems => prevItems.map((item, index) => {
            return item.id != userImportUserId ? item : { ...item, educatorEmail: educatorEmail }
        }))
    }

    const onChangeUsername = (event) => {
        let userImportUserId = event.target.getAttribute("data-id");
        let username = event.target.value;

        setUserImportUsers(prevItems => prevItems.map((item, index) => {
            return item.id != userImportUserId ? item : { ...item, username: username }
        }))
    }

    const onChangeEmail = (event) => {
        let userImportUserId = event.target.getAttribute("data-id");
        let email = event.target.value;

        setUserImportUsers(prevItems => prevItems.map((item, index) => {
            return item.id != userImportUserId ? item : { ...item, email: email }
        }))
    }


    const importClickHandler = useCallback(() => {

        // todo 1. disable click button.
        // todo 2. should we show a loader?
        // todo 3. save and persist the data and then update the state.
        // todo 4. need to wire in onChange as well on each form field so you can update and modify the values

        for (let user of userImportUsers) {

            let url = window.Routing.generate("user_import_save_user", {
                id: user.id
            });

            let data = {
                user: user
            }

            api.post(url, data)
                .then((response) => {
                    debugger;
                    if (response.statusCode < 300) {
                        debugger;

                        let newItem = response.responseBody.userImportUser;
                        let userImportUserId = response.responseBody.userImportUser.id;
                        setUserImportUsers(prevItems => prevItems.map((item, index) => {
                            return item.id !== userImportUserId ? item : { ...newItem }
                        }))

                        // option 2?

                        /*  let userImportUserId = response.responseBody.userImportUser.id;

                          if (usersToImport.length > 0) {
                              setUserImportUsers(usersToImport.filter((item, index) => item.id !== userImportUserId));
                          }*/

                    } else {
                        debugger;

                        let userImportUserId = response.responseBody.userImportUser.id;
                        let newItem = response.responseBody.userImportUser;
                        let errors = response.responseBody.errors;

                        setUserImportUsers(prevItems => prevItems.map((item, index) => {
                            return item.id !== userImportUserId ? item : { ...newItem }
                        }))

                    }
                })
                .catch((e) => {
                })

        }

    }, [userImportUsers]);


    const renderStudentForm = (userImportUser, index) => {

        debugger;

        return (
            <StudentForm
                id={userImportUser.id}
                firstName={userImportUser.firstName}
                lastName={userImportUser.lastName}
                graduatingYear={userImportUser.graduatingYear}
                educatorEmail={userImportUser.educatorEmail}
                tempPassword={userImportUser.tempPassword}
                username={userImportUser.username}
                errors={userImportUser.errors}
                isImported={userImportUser.isImported}
                onChangeFirstName={onChangeFirstName}
                onChangeLastName={onChangeLastName}
                onChangeGraduatingYear={onChangeGraduatingYear}
                onChangeEducatorEmail={onChangeEducatorEmail}
                onChangeUsername={onChangeUsername}
                key={index}
            />
        );
    }

    const renderEducatorForm = (userImportUser) => {
        return (
            <EducatorForm
                id={userImportUser.id}
                firstName={userImportUser.firstName}
                lastName={userImportUser.lastName}
                email={userImportUser.email}
                tempPassword={userImportUser.tempPassword}
                errors={userImportUser.errors}
                onChangeFirstName={onChangeFirstName}
                onChangeLastName={onChangeLastName}
                onChangeEmail={onChangeEmail}
            />
        );
    }

    return (
        <div className="uk-grid">
            {userImportUsers.filter(userImportUser => !userImportUser.isImported).map((userImportUser, index) => {
                return (
                    userImport.type === 'Student' && renderStudentForm(userImportUser, index) ||
                    userImport.type === 'Educator' && renderEducatorForm(userImportUser, index)
                );
            })}

            <button onClick={importClickHandler} style={{"position": "absolute", "top": "80px", "right": "40px"}} type="button" className="uk-button uk-button-primary">Start Import</button>
        </div>
    );

}

App.propTypes = {
    userImportUuid: PropTypes.string.isRequired
};

App.defaultProps = {};
