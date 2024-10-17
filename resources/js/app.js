import './bootstrap';
var scrollToSection = function (event) {
    setTimeout(() => {
        const activeSidebarItem = document.querySelector('.fi-sidebar-item-active');
        const sidebarWrapper = document.querySelector('.fi-sidebar-nav')

        if (
            typeof (sidebarWrapper) != 'undefined' && sidebarWrapper != null
            && typeof (activeSidebarItem) != 'undefined' && activeSidebarItem != null
        ) {
            sidebarWrapper.style.scrollBehavior = 'smooth';
            sidebarWrapper.scrollTo(0, activeSidebarItem.offsetTop - 250)
        }

    }, 1)
};

document.addEventListener('livewire:navigated', scrollToSection);
document.addEventListener('DOMContentLoaded', scrollToSection);
