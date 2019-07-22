import React from "react";
import ReactDOM from "react-dom";
import LessonListing from "../../components/LessonListing/LessonListing";

const lesson_listings = document.getElementsByClassName("lesson-listing");
for( let i = 0; i < lesson_listings.length; i++) {

    const lesson = JSON.parse(lesson_listings[i].getAttribute("data-lesson"));

    if ( lesson.hasOwnProperty('id') ) {
        ReactDOM.render(
            <LessonListing
                description={'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud'}
                id={lesson.id}
                image={lesson.thumbnailImageURL}
                title={lesson.title} />,
            lesson_listings[i]
        );
    }
}