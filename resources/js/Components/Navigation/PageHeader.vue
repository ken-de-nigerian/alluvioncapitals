<script setup>
    import {usePage} from "@inertiajs/vue3";
    import AccountDropdown from "../../Components/Navigation/AccountDropdown.vue";
    import Hamburger from "../../Components/Navigation/Hamburger.vue";
    import NavbarLogo from "../../Components/Navigation/NavbarLogo.vue";
    import ThemeSwitcher from "../../Components/Navigation/ThemeSwitcher.vue";
    import SearchButton from "../../Components/Navigation/SearchButton.vue";
    import MainNavigation from "../../Components/Navigation/MainNavigation.vue";
    import {onMounted, computed} from "vue";
    import initThemeSwitcher from "../../Components/Utilities/ThemeSwitcher.js";

    const page = usePage();

    const user = page.props.auth?.user;

    const navbarClasses = computed(() => {
        const isNotHomeOrCategory = !page.component.startsWith('Homepage/') && !page.component.startsWith('Categories/');
        return [
            'navbar',
            'navbar-expand-lg',
            'z-fixed',
            'px-0',
            {
                'bg-body': isNotHomeOrCategory,
                'navbar-sticky': isNotHomeOrCategory,
                'sticky-top': isNotHomeOrCategory
            }
        ];
    });

    onMounted(() => {
        initThemeSwitcher();
    });
</script>

<template>
    <header :class="navbarClasses" data-sticky-element="">
        <div class="container flex-nowrap">
            <!-- Mobile offcanvas menu toggler (Hamburger) -->
            <Hamburger/>

            <!-- Navbar brand (Logo) -->
            <NavbarLogo/>

            <!-- Main navigation that turns into offcanvas on screens < 992px wide (lg breakpoint) -->
            <MainNavigation/>

            <!-- Button group -->
            <div class="d-flex align-items-center">
                <!-- Theme switcher (light/dark/auto) -->
                <ThemeSwitcher/>

                <!-- Search toggle button -->
                <SearchButton/>

                <!-- Account button (logged in state) with dropdown visible on screens > 768px wide (md breakpoint) -->
                <AccountDropdown :user="user"/>
            </div>
        </div>
    </header>
</template>
