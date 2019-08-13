export function truncate(input, limit = 200) {
    if (input.length > limit) {
        return input.substring(0, limit) + '...';
    } else {
        return input;
    }
}