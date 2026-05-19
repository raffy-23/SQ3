(function () {
    'use strict';

    if (!window.SideQuest) {
        console.error('SideQuest core not loaded.');
        return;
    }

    var initAll = function () {
        if (window.SideQuest.initSidebar) { window.SideQuest.initSidebar(); }
        if (window.SideQuest.initInfiniteFeeds) { window.SideQuest.initInfiniteFeeds(); }
        if (window.SideQuest.initPostMenus) { window.SideQuest.initPostMenus(); }
        if (window.SideQuest.initReactionPickers) { window.SideQuest.initReactionPickers(); }
        if (window.SideQuest.initAjaxForms) { window.SideQuest.initAjaxForms(); }
        if (window.SideQuest.initComposerMediaPicker) { window.SideQuest.initComposerMediaPicker(); }
        if (window.SideQuest.initRecommendations) { window.SideQuest.initRecommendations(); }
        if (window.SideQuest.initFollowSystem) { window.SideQuest.initFollowSystem(); }
        if (window.SideQuest.initNotifications) { window.SideQuest.initNotifications(); }
        if (window.SideQuest.initReactorsDialog) { window.SideQuest.initReactorsDialog(); }
        if (window.SideQuest.initLiveSearch) { window.SideQuest.initLiveSearch(); }
        if (window.SideQuest.initCustomDropdowns) { window.SideQuest.initCustomDropdowns(); }
        if (window.SideQuest.initCustomDatePickers) { window.SideQuest.initCustomDatePickers(); }
        if (window.SideQuest.initTwoFactorTabs) { window.SideQuest.initTwoFactorTabs(); }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
