import { createStore } from "vuex";
import api from "../api"; // Import your API service

// Load user permissions from localStorage or sessionStorage during store initialization
const userPermissions = JSON.parse(localStorage.getItem('userPermissions')) || [];

// Create a new store instance.
const store = createStore({
    state() {
        return {
            pageTitle: 'Default Title', // Initial title
            message: "welcome",
            option_groups: [],
            categories: [],
            permissions: [],
            roles: [],
            authors: [],
            publishers: [],
            suppliers: [],
            customers: [],
            payment_methods: [],
            books: [],
            user: {
                permissions: userPermissions,
            },
        };
    },
    getters: {
        getPemissions: (state) => {
            api.fetchPermissions().then((permissions) => {
                // console.log(state.permissions);
                state.permissions = permissions;
            });
            return state.permissions;
            // console.log(state.permissions);
            // let formatedPermission = state.permissions.map((permission) => {
            //     const str = `${permission.name}`;
            //     const formatedName = str.charAt(0).toUpperCase() + str.slice(1);
            //     return {
            //         id: permission.id,
            //         name: formatedName,
            //     };
            // });
            // return formatedPermission;
        },
        getRoles: (state) => {
            api.fetchRoles().then((roles) => {
                state.roles = roles;
            });
            return state.roles;
        },
        getAuthors: (state) => {
            api.fetchAuthors().then((authors) => {
                state.authors = authors;
            });
            return state.authors;
        },
        getPublishers: (state) => {
            api.fetchPublishers().then((publishers) => {
                state.publishers = publishers;
            });
            return state.publishers;
        },
        getSuppliers: (state) => {
            api.fetchSuppliers().then((suppliers) => {
                state.suppliers = suppliers;
            });
            return state.suppliers;
        },
        getCustomers: (state) => {
            api.fetchCustomers().then((customers) => {
                state.customers = customers;
            });
            return state.customers;
        },
        getPaymentMethods: (state) => {
            api.fetchPaymentMethods().then((payment_methods) => {
                state.payment_methods = payment_methods;
            });
            return state.payment_methods;
        },
        hasPermission: (state) => (permission) => {
            return state.user.permissions.includes(`${permission}`);
        },
    },
    mutations: {
        setPageTitle(state, title) {
            state.pageTitle = title;
          },
        setUserPermissions(state, permissions) {
            state.user.permissions = permissions;
            localStorage.setItem('userPermissions', JSON.stringify(permissions));
            // console.log('User Permissions:', permissions);
        },
        setRoles(state, roles) {
            state.user.roles = roles;
        },
        setOptionGroups(state, option_groups) {
            state.option_groups = option_groups;
        },
        setCategories(state, categories) {
            state.categories = categories;
        },
        setBooks(state, books) {
            state.books = books;
        },
    },
    actions: {
        fetchUserPermissions({ commit }) {
            // Replace this with the actual API call to fetch user permissions
            // e.g., using axios or fetch
            const userId = User.id();
            if (userId) {
                api.fetchUserPermissions().then((permissions) => {
                    if (permissions) {
                        commit("setUserPermissions", permissions);
                    }
                });
            }
        },
        fetchRoles({ commit }) {
            // e.g., using axios or fetch
            api.fetchRoles().then((roles) => {
                commit("setRoles", roles);
            });
        },
        fetchOptionGroups({ commit }) {
            // e.g., using axios or fetch
            api.fetchOptionGroups().then((option_groups) => {
                commit("setOptionGroups", option_groups);
            });
        },
        fetchCategories({ commit }) {
            // e.g., using axios or fetch
            api.fetchCategories().then((categories) => {
                commit("setCategories", categories);
            });
        },
        fetchBooks({ commit }) {
            // e.g., using axios or fetch
            api.fetchBooks().then((books) => {
                commit("setBooks", books);
            });
        },
    },
});
export default store;
