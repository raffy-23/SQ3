(function () {
    'use strict';
    window.SideQuest.initTwoFactorTabs = function () {
        document.querySelectorAll('[data-two-factor-tabs]').forEach((root) => {
            const triggers = root.querySelectorAll('[data-two-factor-trigger]');
            const panels = document.querySelectorAll('[data-two-factor-panel]');
            triggers.forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    const mode = trigger.dataset.twoFactorTrigger;
                    triggers.forEach((button) => button.classList.toggle('is-active', button === trigger));
                    panels.forEach((panel) => {
                        panel.hidden = panel.dataset.twoFactorPanel !== mode;
                    });
                });
            });
        });
    };
})();
