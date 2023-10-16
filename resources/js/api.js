// api.js

import axios from "axios";
const token = localStorage.getItem("token");
if (!token) {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    if (window.location.href != "http://localhost:8000/") {
        window.location.replace("/"); // go to '/login';
        // this.$router.push({ name: 'login'})
    }
}
axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

const apiClient = axios.create({
    baseURL: "/api", // Adjust the base URL as per your API configuration
});

export default {
    fetchUserPermissions() {
        const userId = User.userId();
        if(userId){
            return apiClient.get(`/user/${userId}/permissions`,
             {
                params: {
                  timestamp: Date.now(), // Add a timestamp query parameter
                },
              }
              ).then((response) => {
                console.log(response.data.permissions);
                return response.data.permissions;
            });
        }
    },
    fetchPermissions() {
        return apiClient.get("/get-permissions", {
            params: {
              timestamp: Date.now(), // Add a timestamp query parameter
            },
          }).then(function (response) {
            return response.data;
        });
    },
    fetchRoles() {
        return apiClient.get("/get-roles").then(function (response) {
            return response.data;
        });
    },
    fetchOptionGroups() {
        return apiClient.get("/get-option-groups").then(function (response) {
            return response.data;
        });
    },
    fetchCategories() {
        return apiClient.get("/get-categories").then(function (response) {
            return response.data;
        });
    },
    fetchAuthors(){
        return apiClient.get("/get-authors").then(function (response) {
            return response.data;
        });
    },
    fetchPublishers(){
        return apiClient.get("/get-publishers").then(function (response) {
            return response.data;
        });
    },
    fetchSuppliers(){
        return apiClient.get("/get-suppliers").then(function (response) {
            return response.data;
        });
    },
    fetchCustomers(){
        return apiClient.get("/get-customers").then(function (response) {
            return response.data;
        });
    },
    fetchPaymentMethods(){
        return apiClient.get("/get-payment-methods").then(function (response) {
            return response.data;
        });
    },
    fetchBooks(){
        return apiClient.get("/get-books").then(function (response) {
            return response.data;
        });
    },
};
