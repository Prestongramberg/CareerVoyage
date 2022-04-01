import React, {useCallback} from "react";

const StudentForm = ({
                         firstName,
                         lastName,
                         graduatingYear,
                         educatorEmail,
                         username,
                         tempPassword,
                         id,
                         errors,
                         isImported,
                         onChangeFirstName,
                         onChangeLastName,
                         onChangeGraduatingYear,
                         onChangeEducatorEmail,
                         onChangeUsername
                     }) => {


    return (
        <div key={id} className="uk-width-1-1">

            <div className="uk-grid uk-margin-bottom">

                <div className="uk-width-1-6">
                    <label>First Name<span className="uk-text-danger">*</span></label>
                    <input data-id={id} onChange={onChangeFirstName} value={firstName} type="text"
                           className="uk-input"/>
                    <div className="uk-text-danger">{errors?.firstName}</div>
                </div>
                <div className="uk-width-1-6">
                    <label>Last Name<span className="uk-text-danger">*</span></label>
                    <input data-id={id} onChange={onChangeLastName} value={lastName} type="text" className="uk-input"/>
                    <div className="uk-text-danger">{errors?.lastName}</div>
                </div>
                <div className="uk-width-1-6">
                    <label>Graduating Year<span className="uk-text-danger">*</span></label>
                    <input data-id={id} onChange={onChangeGraduatingYear} value={graduatingYear} type="text" className="uk-input"/>
                    <div className="uk-text-danger">{errors?.graduatingYear}</div>
                </div>
                <div className="uk-width-1-6">
                    <label>Educator Email<span className="uk-text-danger">*</span></label>
                    <input data-id={id} onChange={onChangeEducatorEmail} value={educatorEmail} type="text" className="uk-input"/>
                    <div className="uk-text-danger">{errors?.educatorEmail}</div>
                </div>
                <div className="uk-width-1-6">
                    <label>Username<span className="uk-text-danger">*</span></label>
                    <input data-id={id} onChange={onChangeUsername} value={username} type="text" className="uk-input"/>
                    <div className="uk-text-danger">{errors?.username}</div>
                </div>
                <div className="uk-width-1-6">
                    <label>Temp Password<span className="uk-text-danger">*</span></label>
                    <input readOnly={true} value={tempPassword} type="text" className="uk-input"/>
                    <div className="uk-text-danger">{errors?.tempPassword}</div>
                </div>
            </div>
        </div>
    );
}

export default StudentForm;
