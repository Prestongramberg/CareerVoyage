import React, {useState, useEffect, useRef, useCallback} from "react"
import PropTypes from "prop-types";
import * as api from "../../utilities/api/api";
import StudentForm from "./components/StudentForm";
import EducatorForm from "./components/EducatorForm";
import Loader from "../../components/Loader/Loader";

export default function App(props) {

    useEffect(() => {
        // Some initialization logic here
        setIsLoading(true);
        loadUserImport();
    }, []);

    const [userImportUsers, setUserImportUsers] = useState([]);
    const [userImport, setUserImport] = useState({});
    const [isLoading, setIsLoading] = useState(false);
    const [isImportStarted, setIsImportStarted] = useState(false);
    const [stopImport, setStopImport] = useState(false);
    const [totalItems, setTotalItems] = useState(0);
    // @see https://stackoverflow.com/questions/57847594/react-hooks-accessing-up-to-date-state-from-within-a-callback
    const stopImportRef = useRef();
    stopImportRef.current = stopImport;

    const loadUserImport = () => {

        let url = window.Routing.generate("user_import_get", {
            uuid: props.userImportUuid
        });

        api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    debugger;
                    let items = response.responseBody.userImport.userImportUsers;
                    setTotalItems(items.length);
                    items = items.filter(item => !item.isImported);
                    setUserImportUsers(items);
                    setUserImport(response.responseBody.userImport);
                    setIsLoading(false);
                }
            })
            .catch((e) => {
            })

    }

    const onChangeFirstName = (event) => {
        let userImportUserId = event.target.getAttribute("data-id");
        let firstName = event.target.value;

        setUserImportUsers(prevItems => prevItems.map((item, index) => {
            return item.id != userImportUserId ? item : {...item, firstName: firstName}
        }))
    }

    const onChangeLastName = (event) => {
        let userImportUserId = event.target.getAttribute("data-id");
        let lastName = event.target.value;

        setUserImportUsers(prevItems => prevItems.map((item, index) => {
            return item.id != userImportUserId ? item : {...item, lastName: lastName}
        }))
    }

    const onChangeGraduatingYear = (event) => {
        let userImportUserId = event.target.getAttribute("data-id");
        let graduatingYear = event.target.value;

        setUserImportUsers(prevItems => prevItems.map((item, index) => {
            return item.id != userImportUserId ? item : {...item, graduatingYear: graduatingYear}
        }))
    }

    const onChangeEducatorEmail = (event) => {
        let userImportUserId = event.target.getAttribute("data-id");
        let educatorEmail = event.target.value;

        setUserImportUsers(prevItems => prevItems.map((item, index) => {
            return item.id != userImportUserId ? item : {...item, educatorEmail: educatorEmail}
        }))
    }

    const onChangeUsername = (event) => {
        let userImportUserId = event.target.getAttribute("data-id");
        let username = event.target.value;

        setUserImportUsers(prevItems => prevItems.map((item, index) => {
            return item.id != userImportUserId ? item : {...item, username: username}
        }))
    }

    const onChangeEmail = (event) => {
        let userImportUserId = event.target.getAttribute("data-id");
        let email = event.target.value;

        setUserImportUsers(prevItems => prevItems.map((item, index) => {
            return item.id != userImportUserId ? item : {...item, email: email}
        }))
    }

    const stopImportClickHandler = useCallback(() => {
        setStopImport(true);
    }, []);

    const importClickHandler = useCallback(async () => {

        setIsImportStarted(true);

        debugger;

        // todo 1. disable click button.
        // todo 2. should we show a loader?
        // todo 3. save and persist the data and then update the state.
        // todo 4. need to wire in onChange as well on each form field so you can update and modify the values

        let usersToImport = userImportUsers.filter(userImportUser => !userImportUser.isImported);

        debugger;
        const chunkSize = 100;
        for (let i = 0; i < usersToImport.length; i += chunkSize) {
            const chunk = usersToImport.slice(i, i + chunkSize);

            debugger;

            if (stopImportRef.current) {
                setIsImportStarted(false);
                setStopImport(false);
                alert("Stopping Import!");
                break;
            }

            let url = window.Routing.generate("user_import_save_user", {
                uuid: props.userImportUuid
            });

            let data = {
                users: chunk
            }

            debugger;
            let response = await api.post(url, data);

            debugger;

            if (response.statusCode < 300) {

                debugger;
                let users = response.responseBody.data;

                setUserImportUsers((prevItems) => {

                    debugger;

                    for (let newItem of users) {

                        let item = prevItems.find((item) => {
                            return item.id === newItem.id;
                        })

                        item.isImported = newItem.isImported;
                        item.errors = newItem.errors;
                    }

                    return [...prevItems];
                })

            }

        }

        setIsImportStarted(false);
        setStopImport(false);

    }, [userImportUsers, isImportStarted]);


    const renderStudentForm = (userImportUser, index) => {

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

    if (isLoading) {
        return (
            <div className="uk-width-1-1 uk-align-center">
                <Loader/>
            </div>
        );
    }

    const importedUserCount = () => {
        return userImportUsers.filter(userImportUser => userImportUser.isImported).length;
    }

    const importedUserPercentage = () => {
        let percent = userImportUsers.filter(userImportUser => userImportUser.isImported).length / userImportUsers.length;
        return Math.floor(percent * 100) + "%";
    }

    return (
        <div className="uk-grid">
            <div className={isImportStarted ? 'uk-width-1-1 uk-margin' : 'uk-width-1-1 uk-margin uk-hidden'}>
                <progress id="js-progressbar" className="uk-progress" value={importedUserCount()}
                          max={totalItems}></progress>
                <div>{importedUserPercentage()}</div>
            </div>

            {userImportUsers.filter(userImportUser => !userImportUser.isImported).map((userImportUser, index) => {
                return (
                    userImport.type === 'Student' && renderStudentForm(userImportUser, index) ||
                    userImport.type === 'Educator' && renderEducatorForm(userImportUser, index)
                );
            })}

            <button disabled={isImportStarted} onClick={importClickHandler}
                    style={{"position": "absolute", "top": "80px", "right": "40px"}} type="button"
                    className={isImportStarted ? 'uk-button uk-button-primary uk-hidden' : 'uk-button uk-button-primary'}>Start
                Import
            </button>
            <button onClick={stopImportClickHandler} style={{"position": "absolute", "top": "80px", "right": "40px"}}
                    type="button"
                    className={isImportStarted ? 'uk-button uk-button-danger' : 'uk-button uk-button-danger uk-hidden'}>Stop
                Import
            </button>
        </div>
    );

}

App.propTypes = {
    userImportUuid: PropTypes.string.isRequired
};

App.defaultProps = {};
