<template>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark static-top">
        <div class="container">
            <a class="navbar-brand" :href="home_url">Go Back To Main Site</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item ">
                        <a class="nav-link" href="https://www.linkedin.com/in/mohsin-hassan-559645a4/" target="_blank">Author LinkedIn
                            <span class="sr-only">Author LinkedIn</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://github.com/mohsinhassan618" target="_blank">Author Git Hub</a>
                        <span class="sr-only">Author Git Hub</span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script>
    export default {
        data: function () {
            return {
                home_url:wp_rest_api.home_url,
            }
        },
        mounted: function () {
        },
    };
</script>

<style lang="css" scoped>
</style>