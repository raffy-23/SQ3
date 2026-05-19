(function () {
    'use strict';
    window.SideQuest.initLiveSearch = function () {
        document.querySelectorAll('[data-live-search]').forEach((input) => {
            const target = document.querySelector(input.dataset.liveSearchTarget || '');
            if (!target) {
                return;
            }

            let timeout;
            input.addEventListener('input', () => {
                clearTimeout(timeout);
                const query = input.value.trim();
                if (query.length < 2) {
                    target.hidden = true;
                    target.innerHTML = '';
                    return;
                }

                timeout = setTimeout(async () => {
                    try {
                        const url = `${input.dataset.liveSearch}?q=${encodeURIComponent(query)}`;
                        const response = await fetch(url, { credentials: 'same-origin' });
                        const results = await response.json();
                        if (!results.length) {
                            target.hidden = false;
                            target.innerHTML = '<p class="sq-muted">No quick matches found.</p>';
                            return;
                        }

                        target.hidden = false;
                        target.innerHTML = results.map((user) => `
                            <a href="${window.SideQuest.appUrl(`u/${encodeURIComponent(user.username)}`)}" class="sq-live-result">
                                ${user.profile_picture_url
                                    ? `<img src="${user.profile_picture_url}" alt="${user.full_name}" class="sq-avatar sq-avatar-sm">`
                                    : `<span class="sq-avatar sq-avatar-sm sq-avatar-fallback">${user.full_name.split(' ').map((part) => part[0] || '').join('').slice(0, 2).toUpperCase()}</span>`}
                                <div>
                                    <div class="sq-user-name">${user.full_name}</div>
                                    <div class="sq-user-handle">@${user.username}</div>
                                </div>
                            </a>
                        `).join('');
                    } catch (error) {
                        console.error(error);
                    }
                }, 180);
            });
        });
    };
})();
