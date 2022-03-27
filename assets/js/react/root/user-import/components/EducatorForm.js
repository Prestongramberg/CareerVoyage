import React, {useCallback} from "react";

const EducatorForm = ({
                          firstName,
                          lastName,
                          email,
                          tempPassword,
                          id,
                          errors,
                          onChangeFirstName,
                          onChangeLastName,
                          onChangeEmail
                      }) => {


    return (
        <div key={id} className="uk-width-1-1">

            <div className="uk-grid uk-margin-bottom">

                <div className="uk-width-1-6">
                    <label>First Name<span className="uk-text-danger">*</span></label>
                    <input data-id={id} onChange={onChangeFirstName} value={firstName} type="text" className="uk-input"/>
                    <div className="uk-text-danger">{errors?.firstName}</div>
                </div>
                <div className="uk-width-1-6">
                    <label>Last Name<span className="uk-text-danger">*</span></label>
                    <input data-id={id} onChange={onChangeLastName} value={lastName} type="text" className="uk-input"/>
                    <div className="uk-text-danger">{errors?.lastName}</div>
                </div>
                <div className="uk-width-1-6">
                    <label>Email<span className="uk-text-danger">*</span></label>
                    <input data-id={id} onChange={onChangeEmail} value={email} type="text" className="uk-input"/>
                    <div className="uk-text-danger">{errors?.email}</div>
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

export default EducatorForm;
