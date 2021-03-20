import React, { Component } from "react";
import PropTypes from "prop-types";
import * as api  from '../../utilities/api/api'

class TeachLesson extends Component {

    constructor(props) {
        super(props);
        const methods = ["renderBasedOnTeachable", "teachLesson", "unTeachLesson"];
        methods.forEach(method => (this[method] = this[method].bind(this)));

        // Only used on Root Binding
        this.state = {
            isTeachable: props.isTeachable
        }
    }

    render() {
        return this.renderBasedOnTeachable( this.props.boundByProps ? this.props.isTeachable : this.state.isTeachable );
    }

    renderBasedOnTeachable( isTeachable ) {

        const staticText = this.props.isTeacher ? "Topics I want Taught" : "Topics I can Teach";

        if( isTeachable ) {
            return <span className="js-un-teach-lesson" data-uk-tooltip={`title: Remove from ${staticText}`} onClick={this.unTeachLesson}>
                        <i className="fa fa-graduation-cap" aria-hidden="true"></i>
                    </span>
        } else {
            return <span className="js-teach-lesson" data-uk-tooltip={`title: Add to ${staticText}`} onClick={this.teachLesson}>
                        <i style={{ opacity: 0.5 }} className="fa fa-graduation-cap" aria-hidden="true"></i>
                    </span>
        }
    }

    unTeachLesson() {

        const url = window.Routing.generate("unteach_lesson", {id: this.props.id});

        return api.post(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    this.setState({
                        isTeachable: !this.state.isTeachable
                    });
                    this.props.lessonIsNowUnteachable(this.props.id);
                }  else {
                    window.Pintex.notification("Unable to unteach topic. Please try again.");
                }
            })
            .catch(()=> {
                window.Pintex.notification("Unable to unteach topic. Please try again.");
            });
    }

    teachLesson() {
        const url = window.Routing.generate("teach_lesson", {id: this.props.id});

        return api.post(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    this.setState({
                        isTeachable: !this.state.isTeachable
                    });
                    this.props.lessonIsNowTeachable(this.props.id);
                }  else {
                    window.Pintex.notification("Unable to teach topic. Please try again.");
                }
            })
            .catch(()=> {
                window.Pintex.notification("Unable to teach topic. Please try again.");
            });
    }
}

TeachLesson.propTypes = {
    id: PropTypes.number,
    isTeachable: PropTypes.bool,
    isTeacher: PropTypes.bool,
    lessonIsNowTeachable: PropTypes.func,
    lessonIsNowUnteachable: PropTypes.func,
    boundByProps: PropTypes.bool
};

TeachLesson.defaultProps = {
    boundByProps: false,
    isTeacher: false,
    lessonIsNowTeachable: () => {},
    lessonIsNowUnteachable: () => {}
};

export default TeachLesson;
