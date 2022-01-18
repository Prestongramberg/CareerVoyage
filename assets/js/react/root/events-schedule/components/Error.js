import {Field} from "react-final-form";
import React from "react";

const Error = ({name}) => (
    <div>
        <Field
            name={name}
            subscription={{touched: true, error: true}}
            render={({meta: {touched, error}}) =>
                touched && error ? <span style={{color: "#f0506e"}}>{error}</span> : null
            }
        />
    </div>
)

export default Error;