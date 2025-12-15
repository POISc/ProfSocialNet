document.querySelectorAll('.post-reactions').forEach(postEl => {
    const postId = postEl.dataset.id;
    const csrfToken = postEl.dataset.token;

    const likeBtn = postEl.querySelector('.like-button');
    const dislikeBtn = postEl.querySelector('.dislike-button');

    likeBtn.addEventListener('click', () => sendReaction(postId, 'like', csrfToken, postEl));
    dislikeBtn.addEventListener('click', () => sendReaction(postId, 'dislike', csrfToken, postEl));
});

function sendReaction(postId, reactionType, csrfToken, postEl) {
    fetch(`/post/react/${postId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: new URLSearchParams({ reactionType, _token: csrfToken })
    })
    .then(response => response.json())
    .then(data => {
        
        postEl.querySelector('.like-count').textContent = data.likes;
        postEl.querySelector('.dislike-count').textContent = data.dislikes;

        const likeBtn = postEl.querySelector('.like-button');
        const dislikeBtn = postEl.querySelector('.dislike-button');

        likeBtn.querySelector('.like-count').textContent = data.likes;
        dislikeBtn.querySelector('.dislike-count').textContent = data.dislikes;

        if (data.userReaction === 'like') {
            likeBtn.classList.add('active-element');
            dislikeBtn.classList.remove('active-element');
        } else if (data.userReaction === 'dislike') {
            dislikeBtn.classList.add('active-element');
            likeBtn.classList.remove('active-element');
        } else {
            likeBtn.classList.remove('active-element');
            dislikeBtn.classList.remove('active-element');
        }
    })
    .catch(err => console.error('Ошибка запроса:', err));
}
