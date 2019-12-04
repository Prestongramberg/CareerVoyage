import React from "react";
import ReactDOM from "react-dom";
import TeachLesson from "../../components/TeachLesson/TeachLesson";

const lesson_teach = document.getElementsByClassName("react-teach-lesson");
for( let i = 0; i < lesson_teach.length; i++) {

    const isTeacher = parseInt(lesson_teach[i].getAttribute("data-teacher"));
    const lessonId = parseInt(lesson_teach[i].getAttribute("data-lesson-id"));
    const lessonIsTeachable = !!lesson_teach[i].getAttribute("data-lesson-teachable");

    ReactDOM.render(
        <TeachLesson
            id={lessonId}
            isTeachable={lessonIsTeachable}
            isTeacher={isTeacher}
        />,
        lesson_teach[i]
    );
}