import "./bootstrap";

import { createApp } from "vue";

import App from "./App.vue";

// pagination
import { Bootstrap5Pagination } from "laravel-vue-pagination";

// form validation
import { Form } from "vform";
window.Form = Form;
import { Button, HasError, AlertError } from "vform/src/components/bootstrap5";
// Import User Class
//  import User from './Helpers/User';
//  window.User = User

// vuex
import store from "./store/index.js";
// routes
import router from "./router.js";
const appName = window.env.APP_NAME;
router.beforeEach((to, from, next) => {
    // Update the page title based on the route's meta field
    document.title = appName + " - " + to.meta.title || "Home";
    let pageTitle = to.meta.title || "";
    store.commit('setPageTitle', pageTitle);
    next();
});

// Import Notification Class
import Notification from "./Helpers/Notification";
window.Notification = Notification;


import Swal from "sweetalert2";
window.Swal = Swal;
const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
});
window.Toast = Toast;
// window.Reload = createApp;

// Import components
import VueProgressBar from "@aacassandra/vue3-progressbar";
import Loader from "./components/Loader.vue";
import TableLoader from "./components/TableLoader.vue";
import SaveButton from "./components/SaveButton.vue";
import SaveChangesButton from "./components/SaveChangesButton.vue";
import RefreshButton from "./components/RefreshButton.vue";
import ResetButton from "./components/ResetButton.vue";
import LoadingSpinner from "./components/LoadingSpinner.vue";
import NoRecordFound from "./components/NoRecordFound.vue";
const options = {
    color: "red",
    failedColor: "#874b4b",
    thickness: "5px",
    transition: {
        speed: "0.2s",
        opacity: "0.9s",
        termination: 300,
    },
    autoRevert: true,
    location: "top",
    inverse: false,
};

const app = createApp(App);
app.component("loader", Loader);
app.component("TableLoader", TableLoader);
app.component("LoadingSpinner", LoadingSpinner);
app.component("NoRecordFound", NoRecordFound);
app.component("pagination", Bootstrap5Pagination);
app.component("save-button", SaveButton);
app.component("save-changes-button", SaveChangesButton);
app.component("refresh-button", RefreshButton);
app.component("reset-button", ResetButton);
app.component("Button", Button);
app.component("HasError", HasError);
app.component("AlertError", AlertError);

app.use(VueProgressBar, options);
app.use(router);
app.use(store);

app.mount("#app");
