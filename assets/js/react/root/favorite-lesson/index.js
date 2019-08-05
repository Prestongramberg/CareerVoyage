import React from "react";
import ReactDOM from "react-dom";
import FavoriteLesson from "../../components/FavoriteLesson/FavoriteLesson";

const lesson_favorites = document.getElementsByClassName("react-favorite-lesson");
for( let i = 0; i < lesson_favorites.length; i++) {

    const lessonId = parseInt(lesson_favorites[i].getAttribute("data-lesson-id"));
    const lessonIsFavorited = !!lesson_favorites[i].getAttribute("data-lesson-favorited");

    ReactDOM.render(
        <FavoriteLesson
            id={lessonId}
            isFavorited={lessonIsFavorited} />,
        lesson_favorites[i]
    );
}