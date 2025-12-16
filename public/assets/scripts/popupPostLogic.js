document.querySelectorAll('.edit-post-button').forEach(btn => {
    btn.addEventListener('click', () => {
        const postId = btn.dataset.id;
        const content = btn.dataset.content;
        const csrfToken = btn.dataset.token;

        const modal = document.getElementById('editPostModal');
        const form = document.getElementById('editPostForm');

        form.action = `/post/change/${postId}`;
        
        form.querySelector('[name="content"]').value = content;
        form.querySelector('[name="_csrf_token"]').value = csrfToken;
        form.querySelector('[name="post_id"]').value = postId;

        modal.classList.remove('hidden')
    });
});

document.getElementById('closeModal').addEventListener('click', () => {
    document.getElementById('editPostModal').classList.add('hidden');
});