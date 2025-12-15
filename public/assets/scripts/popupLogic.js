const modal = document.getElementById('editModal');
const openBtn = document.getElementById('user-edit-button');
const closeBtn = document.getElementById('closeModal');

openBtn.onclick = () => modal.classList.remove('hidden');
closeBtn.onclick = () => modal.classList.add('hidden');