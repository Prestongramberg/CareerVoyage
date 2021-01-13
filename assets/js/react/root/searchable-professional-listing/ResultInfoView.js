import PropTypes from "prop-types";
import React from "react";
import {Result} from "@elastic/react-search-ui/es/containers";

const ResultInfoView = ({
                            className,
                            result,
                            onClickLink,
                            titleField,
                            urlField,
                            ...rest
                        }) => (
    <div className="paging-info">
        <strong>
            hello world!!
        </strong>
    </div>
);

ResultInfoView.propTypes = {
    // todo maybe change these to required?
    result: PropTypes.object,
    onClickLink: PropTypes.func,
    className: PropTypes.string,
    titleField: PropTypes.string,
    urlField: PropTypes.string
};

export default ResultInfoView;