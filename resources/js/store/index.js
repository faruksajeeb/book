import { createStore } from "vuex";
import api from "../api"; // Import your API service

// Load user permissions from localStorage or sessionStorage during store initialization
const userPermissions = JSON.parse(localStorage.getItem("userPermissions")) || [];

// Create a new store instance.
const store = createStore({
    state() {
        return {
            pageTitle: "Default Title", // Initial title
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
                state.permissions = permissions;
            });
            return state.permissions;
        },
        hasPermission: (state) => (requiredPermissions) => {
            const userPermissions = Object.values(state.user.permissions);
            const required = Array.isArray(requiredPermissions)
                ? requiredPermissions
                : [requiredPermissions];
            return required.every((permission) =>
                userPermissions.includes(permission)
            );
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
        }
        
    },
    mutations: {
        setPageTitle(state, title) {
            state.pageTitle = title;
        },
        setUserPermissions(state, permissions) {
            state.user.permissions = permissions;
            localStorage.setItem(
                "userPermissions",
                JSON.stringify(permissions)
            );
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
        async fetchUserPermissions({ commit }) {
            try {
                // Fetch the current user's ID
                const userId = User.id();

                if (!userId) {
                    console.warn(
                        "User ID is not available. Cannot fetch permissions."
                    );
                    return;
                }

                // Make the API call to fetch user permissions
                const permissions = await api.fetchUserPermissions(userId);

                if (permissions) {
                    // Commit the fetched permissions to the Vuex store
                    commit("setUserPermissions", permissions);
                } else {
                    console.warn("No permissions returned from API.");
                }
            } catch (error) {
                console.error("Error fetching user permissions:", error);
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
