import { createWebHistory, createRouter } from "vue-router";
import store from "./store/index";
import User from "./Helpers/User";
window.User = User;

import PermissionDenied from "./components/PermissionDenied.vue";
import Login from "./components/auth/Login.vue";
import Register from "./components/auth/Register.vue";
import ForgetPassword from "./components/auth/ForgetPassword.vue";
import Logout from "./components/auth/Logout.vue";

import Dashboard from "./components/Dashboard.vue";

// User Files
import UserForm from "./components/user_management/user/UserForm.vue";
import ManageUser from "./components/user_management/user/ManageUser.vue";
import ViewUser from "./components/user_management/user/User.vue";

// Role Files
import RoleForm from "./components/user_management/role/RoleForm.vue";
import ManageRole from "./components/user_management/role/ManageRole.vue";
import Role from "./components/user_management/role/Role.vue";

// Permission Files
import AddPermission from "./components/user_management/permission/Create.vue";
import ManagePermission from "./components/user_management/permission/Index.vue";
import EditPermission from "./components/user_management/permission/Edit.vue";

// OptionGroup Files
import OptionGroupForm from "./components/master_data/option_group/OptionGroupForm.vue";
import ManageOptionGroup from "./components/master_data/option_group/ManageOptionGroup.vue";
import ViewOptionGroup from "./components/master_data/option_group/OptionGroup.vue";

// Option Files
import OptionForm from "./components/master_data/option/OptionForm.vue";
import ManageOption from "./components/master_data/option/ManageOption.vue";
import ViewOption from "./components/master_data/option/Option.vue";

// Category Files
import CategoryForm from "./components/master_data/category/CategoryForm.vue";
import ManageCategory from "./components/master_data/category/ManageCategory.vue";
import ViewCategory from "./components/master_data/category/Category.vue";

// SubCategory Files
import SubCategoryForm from "./components/master_data/sub-category/SubCategoryForm.vue";
import ManageSubCategory from "./components/master_data/sub-category/ManageSubCategory.vue";
import ViewSubCategory from "./components/master_data/sub-category/SubCategory.vue";

// Customer Files
import CustomerForm from "./components/customer/CustomerForm.vue";
import ManageCustomer from "./components/customer/ManageCustomer.vue";

// Supplier Files
import SupplierForm from "./components/supplier/SupplierForm.vue";
import ManageSupplier from "./components/supplier/ManageSupplier.vue";

// Author Files
import AuthorForm from "./components/master_data/author/AuthorForm.vue";
import ManageAuthor from "./components/master_data/author/ManageAuthor.vue";

// Publisher Files
import PublisherForm from "./components/master_data/publisher/PublisherForm.vue";
import ManagePublisher from "./components/master_data/publisher/ManagePublisher.vue";

// Book Files
import BookForm from "./components/book/BookForm.vue";
import ManageBook from "./components/book/ManageBook.vue";

// Purchase Files
import PurchaseForm from "./components/purchase/PurchaseForm.vue";
import ManagePurchase from "./components/purchase/ManagePurchase.vue";

// Sale Files
import SaleForm from "./components/sale/SaleForm.vue";
import ManageSale from "./components/sale/ManageSale.vue";

// CustomerPayment Files
import CustomerPaymentForm from "./components/customer_payment/CustomerPaymentForm.vue";
import ManageCustomerPayment from "./components/customer_payment/ManageCustomerPayment.vue";

// Supplier Payment Files
import SupplierPaymentForm from "./components/supplier_payment/SupplierPaymentForm.vue";
import ManageSupplierPayment from "./components/supplier_payment/ManageSupplierPayment.vue";

// Sale Return Files
import SaleReturnForm from "./components/sale_return/SaleReturnForm.vue";
import ManageSaleReturn from "./components/sale_return/ManageSaleReturn.vue";

// Purchase Return Files
import PurchaseReturnForm from "./components/purchase_return/PurchaseReturnForm.vue";
import ManagePurchaseReturn from "./components/purchase_return/ManagePurchaseReturn.vue";

// Damage Item Files
import DamageItemForm from "./components/damage_item/DamageItemForm.vue";
import ManageDamageItem from "./components/damage_item/ManageDamageItem.vue";

// Report Files
import ReportIndex from "./components/report/Index.vue";
import SupplierPaymentReport from "./components/report/supplier_payment/Index.vue";
import CustomerPaymentReport from "./components/report/customer_payment/Index.vue";

import SaleReport from "./components/report/sale/Index.vue";
import CategoryWiseSaleReport from "./components/report/sale/CategoryWiseSale.vue";
import CustomerWiseSaleReport from "./components/report/sale/CustomerWiseSale.vue";

import PurchaseReport from "./components/report/purchase/Index.vue";
import CategoryWisePurchaseReport from "./components/report/purchase/CategoryWisePurchase.vue";
import SupplierWisePurchaseReport from "./components/report/purchase/SupplierWisePurchase.vue";

import StockReport from "./components/report/stock/Index.vue";
import StockAlertReport from "./components/report/stock_alert/Index.vue";
import DamageItemReport from "./components/report/damage_item/Index.vue";
import IncomeStatementReport from "./components/report/income_statement/Index.vue";
import ActivityLogReport from "./components/report/activity_log/Index.vue";

function guard(to, from, next) {
    // or however you store your logged in state
    if (User.loggedIn()) {
        // Check if the route requires authentication
        if (to.meta.requiresAuth) {
            // Check if the user has the required permissions
            if (store.getters.hasPermission(to.meta.requiredPermissions)) {
                next();
            } else {
                // Redirect to a permission denied page or show an error
                next("/permission-denied");
            }
        } else {
            next();
        }
    } else {
        next("/"); // go to '/login';
    }
}
export const routes = [
    {
        name: "permission-denied",
        path: "/permission-denied",
        component: PermissionDenied,
        meta: { title: "Permission Denied!" },
    },
    {
        name: "login",
        path: "/",
        component: Login,
        meta: { title: "Login" },
    },
    {
        name: "register",
        path: "/register",
        component: Register,
        meta: { title: "Register" },
    },
    {
        name: "forget_password",
        path: "/forget_password",
        component: ForgetPassword,
        meta: { title: "Forget Password" },
    },
    {
        name: "dashboard",
        path: "/dashboard",
        component: Dashboard,
        meta: { title: "Dashboard" },
        beforeEnter: guard,
    },
    {
        name: "logout",
        path: "/logout",
        component: Logout,
    },

    // Role Paths
    {
        name: "roles",
        path: "/roles",
        component: ManageRole,
        meta: {
            title: "Manage Roles",
            requiresAuth: true,
            requiredPermissions: ["role.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "role.create",
        path: "/roles/create",
        component: RoleForm,
        meta: {
            title: "Create Role",
            requiresAuth: true,
            requiredPermissions: ["role.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "role.edit",
        path: "/roles/:id/edit",
        component: RoleForm,
        meta: {
            title: "Edit Role",
            requiresAuth: true,
            requiredPermissions: ["role.edit"],
        },
        beforeEnter: guard,
    },
    {
        name: "role.view",
        path: "/roles/:id",
        component: Role,
        meta: {
            title: "View Role",
            requiresAuth: true,
            requiredPermissions: ["role.view"],
        },
        beforeEnter: guard,
    },

    // User Paths
    {
        name: "users",
        path: "/users",
        component: ManageUser,
        meta: {
            title: "Manage Users",
            requiresAuth: true,
            requiredPermissions: ["user.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "user.create",
        path: "/users/create",
        component: UserForm,
        meta: {
            title: "Add User",
            requiresAuth: true,
            requiredPermissions: ["user.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "user.edit",
        path: "/users/:id/edit",
        component: UserForm,
        meta: {
            title: "Edit User",
            requiresAuth: true,
            requiredPermissions: ["user.edit"],
        },
        beforeEnter: guard,
    },
    {
        name: "user.view",
        path: "/users/:id",
        component: ViewUser,
        meta: {
            title: "View User",
            requiresAuth: true,
            requiredPermissions: ["user.view"],
        },
        beforeEnter: guard,
    },

    // Permission Paths
    {
        name: "add-permission",
        path: "/add-permission",
        component: AddPermission,
        meta: {
            title: "Add Permission",
            requiresAuth: true,
            requiredPermissions: ["permission.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "edit-permission",
        path: "/edit-permission/:id",
        component: EditPermission,
        meta: {
            title: "Edit Permission",
            requiresAuth: true,
            requiredPermissions: ["permission.edit"],
        },
        beforeEnter: guard,
    },
    {
        name: "manage-permission",
        path: "/manage-permission",
        component: ManagePermission,
        meta: {
            title: "Manage Permission",
            requiresAuth: true,
            requiredPermissions: ["permission.manage"],
        },
        beforeEnter: guard,
    },
    // Option Group Paths
    {
        name: "option-groups",
        path: "/option-groups",
        component: ManageOptionGroup,
        meta: {
            title: "Manage Option Group",
            requiresAuth: true,
            requiredPermissions: ["option_group.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "option-groups.create",
        path: "/option-groups/create",
        component: OptionGroupForm,
        meta: {
            title: "Add Option Group",
            requiresAuth: true,
            requiredPermissions: ["option_group.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "option-groups.edit",
        path: "/option-groups/:id/edit",
        component: OptionGroupForm,
        meta: {
            title: "Edit Option Group",
            requiresAuth: true,
            requiredPermissions: ["option_group.edit"],
        },
        beforeEnter: guard,
    },
    {
        name: "option-groups.view",
        path: "/option-groups/:id",
        component: ViewOptionGroup,
        meta: {
            title: "View Option Group",
            requiresAuth: true,
            requiredPermissions: ["option_group.view"],
        },
        beforeEnter: guard,
    },
    // Option Paths
    {
        name: "options",
        path: "/options",
        component: ManageOption,
        meta: {
            title: "Manage Options",
            requiresAuth: true,
            requiredPermissions: ["option.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "options.create",
        path: "/options/create",
        component: OptionForm,
        meta: {
            title: "Add Option",
            requiresAuth: true,
            requiredPermissions: ["option.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "options.edit",
        path: "/options/:id/edit",
        component: OptionForm,
        meta: {
            title: "Edit Option",
            requiresAuth: true,
            requiredPermissions: ["option.edit"],
        },
        beforeEnter: guard,
    },
    {
        name: "options.view",
        path: "/options/:id",
        component: ViewOption,
        meta: {
            title: "View Option",
            requiresAuth: true,
            requiredPermissions: ["option.view"],
        },
        beforeEnter: guard,
    },

    // Category Paths
    {
        name: "categories",
        path: "/categories",
        component: ManageCategory,
        meta: {
            title: "Manage Category",
            requiresAuth: true,
            requiredPermissions: ["category.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "categories.create",
        path: "/categories/create",
        component: CategoryForm,
        meta: {
            title: "Add Category",
            requiresAuth: true,
            requiredPermissions: ["category.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "categories.edit",
        path: "/categories/:id/edit",
        component: CategoryForm,
        meta: {
            title: "Edit Category",
            requiresAuth: true,
            requiredPermissions: ["category.edit"],
        },
        beforeEnter: guard,
    },
    {
        name: "categories.view",
        path: "/categories/:id",
        component: ViewCategory,
        meta: {
            title: "View Category",
            requiresAuth: true,
            requiredPermissions: ["category.view"],
        },
        beforeEnter: guard,
    },

    // Sub-Category Paths
    {
        name: "sub-categories",
        path: "/sub-categories",
        component: ManageSubCategory,
        meta: {
            title: "Manage Sub-Category",
            requiresAuth: true,
            requiredPermissions: ["sub_category.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "sub-categories.create",
        path: "/sub-categories/create",
        component: SubCategoryForm,
        meta: {
            title: "Add Sub-Category",
            requiresAuth: true,
            requiredPermissions: ["sub_category.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "sub-categories.edit",
        path: "/sub-categories/:id/edit",
        component: SubCategoryForm,
        meta: {
            title: "Edit Sub-Category",
            requiresAuth: true,
            requiredPermissions: ["sub_category.edit"],
        },
        beforeEnter: guard,
    },
    {
        name: "sub-categories.view",
        path: "/sub-categories/:id",
        component: ViewSubCategory,
        meta: {
            title: "View Sub-Category",
            requiresAuth: true,
            requiredPermissions: ["subcategory.view"],
        },
        beforeEnter: guard,
    },

    // Author Paths
    {
        name: "authors",
        path: "/authors",
        component: ManageAuthor,
        meta: {
            title: "Manage Author",
            requiresAuth: true,
            requiredPermissions: ["author.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "authors.create",
        path: "/authors/create",
        component: AuthorForm,
        meta: {
            title: "Add Author",
            requiresAuth: true,
            requiredPermissions: "author.create",
        },
        beforeEnter: guard,
    },
    {
        name: "authors.edit",
        path: "/authors/:id/edit",
        component: AuthorForm,
        meta: {
            title: "Edit Author",
            requiresAuth: true,
            requiredPermissions: ["author.edit"],
        },
        beforeEnter: guard,
    },

    // Publisher Paths
    {
        name: "publishers",
        path: "/publishers",
        component: ManagePublisher,
        meta: {
            title: "Manage Publisher",
            requiresAuth: true,
            requiredPermissions: ["publisher.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "publishers.create",
        path: "/publishers/create",
        component: PublisherForm,
        meta: {
            title: "Add Publisher",
            requiresAuth: true,
            requiredPermissions: ["publisher.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "publishers.edit",
        path: "/publishers/:id/edit",
        component: PublisherForm,
        meta: {
            title: "Edit Publisher",
            requiresAuth: true,
            requiredPermissions: ["publisher.edit"],
        },
        beforeEnter: guard,
    },

    // Customer Paths
    {
        name: "customers",
        path: "/customers",
        component: ManageCustomer,
        meta: {
            title: "Manage Customer",
            requiresAuth: true,
            requiredPermissions: ["customer.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "customers.create",
        path: "/customers/create",
        component: CustomerForm,
        meta: {
            title: "Add Customer",
            requiresAuth: true,
            requiredPermissions: ["customer.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "customers.edit",
        path: "/customers/:id/edit",
        component: CustomerForm,
        meta: {
            title: "Edit Customer",
            requiresAuth: true,
            requiredPermissions: ["customer.edit"],
        },
        beforeEnter: guard,
    },

    // Supplier Paths
    {
        name: "suppliers",
        path: "/suppliers",
        component: ManageSupplier,
        meta: {
            title: "Manage Supplier",
            requiresAuth: true,
            requiredPermissions: ["supplier.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "suppliers.create",
        path: "/suppliers/create",
        component: SupplierForm,
        meta: {
            title: "Add Supplier",
            requiresAuth: true,
            requiredPermissions: ["supplier.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "suppliers.edit",
        path: "/suppliers/:id/edit",
        component: SupplierForm,
        meta: {
            title: "Edit Supplier",
            requiresAuth: true,
            requiredPermissions: ["supplier.edit"],
        },
        beforeEnter: guard,
    },
    // Book Paths
    {
        name: "books",
        path: "/books",
        component: ManageBook,
        meta: {
            title: "Manage Book",
            requiresAuth: true,
            requiredPermissions: ["book.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "books.create",
        path: "/books/create",
        component: BookForm,
        meta: {
            title: "Add Book",
            requiresAuth: true,
            requiredPermissions: ["book.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "books.edit",
        path: "/books/:id/edit",
        component: BookForm,
        meta: {
            title: "Edit Book",
            requiresAuth: true,
            requiredPermissions: ["book.edit"],
        },
        beforeEnter: guard,
    },
    // Purchase Paths
    {
        name: "purchases",
        path: "/purchases",
        component: ManagePurchase,
        meta: {
            title: "Manage Purchase",
            requiresAuth: true,
            requiredPermissions: ["purchase.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "purchases.create",
        path: "/purchases/create",
        component: PurchaseForm,
        meta: {
            title: "Add Purchase",
            requiresAuth: true,
            requiredPermissions: ["purchase.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "purchases.edit",
        path: "/purchases/:id/edit",
        component: PurchaseForm,
        meta: {
            title: "Edit Purchase",
            requiresAuth: true,
            requiredPermissions: ["purchase.edit"],
        },
        beforeEnter: guard,
    },

    // Sale Paths
    {
        name: "sales",
        path: "/sales",
        component: ManageSale,
        meta: {
            title: "Manage Sale",
            requiresAuth: true,
            requiredPermissions: ["sale.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "sales.create",
        path: "/sales/create",
        component: SaleForm,
        meta: {
            title: "Add Sale",
            requiresAuth: true,
            requiredPermissions: ["sale.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "sales.edit",
        path: "/sales/:id/edit",
        component: SaleForm,
        meta: {
            title: "Edit Sale",
            requiresAuth: true,
            requiredPermissions: ["sale.edit"],
        },
        beforeEnter: guard,
    },

    // Customer Payments
    {
        name: "customer_payments",
        path: "/customer_payments",
        component: ManageCustomerPayment,
        meta: {
            title: "Manage Customer Payment",
            requiresAuth: true,
            requiredPermissions: ["customer_payment.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "customer_payments.create",
        path: "/customer_payments/create",
        component: CustomerPaymentForm,
        meta: {
            title: "Add Customer Payment",
            requiresAuth: true,
            requiredPermissions: ["customer_payment.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "customer_payments.edit",
        path: "/customer_payments/:id/edit",
        component: CustomerPaymentForm,
        meta: {
            title: "Edit Customer Payment",
            requiresAuth: true,
            requiredPermissions: ["customer_payment.edit"],
        },
        beforeEnter: guard,
    },

    // Customer Payments
    {
        name: "supplier_payments",
        path: "/supplier_payments",
        component: ManageSupplierPayment,
        meta: {
            title: "Manage Supplier Payment",
            requiresAuth: true,
            requiredPermissions: ["supplier_payment.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "supplier_payments.create",
        path: "/supplier_payments/create",
        component: SupplierPaymentForm,
        meta: {
            title: "Add Supplier Payment",
            requiresAuth: true,
            requiredPermissions: ["supplier_payment.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "supplier_payments.edit",
        path: "/supplier_payments/:id/edit",
        component: SupplierPaymentForm,
        meta: {
            title: "Edit Supplier Payment",
            requiresAuth: true,
            requiredPermissions: ["supplier_payment.edit"],
        },
        beforeEnter: guard,
    },

    // Purchase Returns
    {
        name: "purchase-returns",
        path: "/purchase-returns",
        component: ManagePurchaseReturn,
        meta: {
            title: "Manage Purchase Return",
            requiresAuth: true,
            requiredPermissions: ["purchase_return.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "purchase-returns.create",
        path: "/purchase-returns/create",
        component: PurchaseReturnForm,
        meta: {
            title: "Add Purchase Return",
            requiresAuth: true,
            requiredPermissions: ["purchase_return.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "purchase-returns.edit",
        path: "/purchase-returns/:id/edit",
        component: PurchaseReturnForm,
        meta: {
            title: "Edit Purchase Return",
            requiresAuth: true,
            requiredPermissions: ["purchase_return.edit"],
        },
        beforeEnter: guard,
    },

    // Sale Returns
    {
        name: "sale-returns",
        path: "/sale-returns",
        component: ManageSaleReturn,
        meta: {
            title: "Manage Sale Return",
            requiresAuth: true,
            requiredPermissions: ["sale_return.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "sale-returns.create",
        path: "/sale-returns/create",
        component: SaleReturnForm,
        meta: {
            title: "Add Sale Return",
            requiresAuth: true,
            requiredPermissions: ["sale_return.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "sale-returns.edit",
        path: "/sale-returns/:id/edit",
        component: SaleReturnForm,
        meta: {
            title: "Edit Sale Return",
            requiresAuth: true,
            requiredPermissions: ["sale_return.edit"],
        },
        beforeEnter: guard,
    },

    // Damage Item
    {
        name: "damage-items",
        path: "/damage-items",
        component: ManageDamageItem,
        meta: {
            title: "Manage Damage Item",
            requiresAuth: true,
            requiredPermissions: ["damage_item.manage"],
        },
        beforeEnter: guard,
    },
    {
        name: "damage-items.create",
        path: "/damage-items/create",
        component: DamageItemForm,
        meta: {
            title: "Add Damage Item",
            requiresAuth: true,
            requiredPermissions: ["damage_item.create"],
        },
        beforeEnter: guard,
    },
    {
        name: "damage-items.edit",
        path: "/damage-items/:id/edit",
        component: DamageItemForm,
        meta: {
            title: "Edit Damage Item",
            requiresAuth: true,
            requiredPermissions: ["damage_item.edit"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports",
        component: ReportIndex,
        name: "reports",
        meta: {
            title: "Reports",
            requiresAuth: true,
            requiredPermissions: ["report.view"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/customer-payment",
        component: CustomerPaymentReport,
        name: "reports/customer_payment",
        meta: {
            title: "Report | Customer Payment",
            requiresAuth: true,
            requiredPermissions: ["report.customer_payment"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/supplier-payment",
        component: SupplierPaymentReport,
        name: "reports/supplier_payment",
        meta: {
            title: "Report | Supplier Payment",
            requiresAuth: true,
            requiredPermissions: ["report.supplier_payment"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/sale",
        component: SaleReport,
        name: "reports/sale",
        meta: {
            title: "Report | Sale",
            requiresAuth: true,
            requiredPermissions: ["report.sale"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/sale/customer-wise-sale",
        component: CustomerWiseSaleReport,
        name: "reports/sale/customer-wise-sale",
        meta: {
            title: "Report | Customer Wise Sale",
            requiresAuth: true,
            requiredPermissions: ["report.sale"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/sale/category-wise-sale",
        component: CategoryWiseSaleReport,
        name: "reports/sale/category-wise-sale",
        meta: {
            title: "Report | Category Wise Sale",
            requiresAuth: true,
            requiredPermissions: ["report.sale"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/purchase",
        component: PurchaseReport,
        name: "reports/purchase",
        meta: {
            title: "Report | Purchase",
            requiresAuth: true,
            requiredPermissions: ["report.purchase"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/purchase/supplier-wise-purchase",
        component: SupplierWisePurchaseReport,
        name: "reports/purchase/supplier-wise-purchase",
        meta: {
            title: "Report | Supplier Wise Purchase",
            requiresAuth: true,
            requiredPermissions: ["report.purchase"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/purchase/category-wise-purchase",
        component: CategoryWisePurchaseReport,
        name: "reports/purchase/category-wise-purchase",
        meta: {
            title: "Report | Category Wise Purchase",
            requiresAuth: true,
            requiredPermissions: ["report.purchase"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/stock",
        component: StockReport,
        name: "reports/stock",
        meta: {
            title: "Report | Stock",
            requiresAuth: true,
            requiredPermissions: ["report.stock"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/stock-alert",
        component: StockAlertReport,
        name: "reports/stock-alert",
        meta: {
            title: "Report | Stock Alert",
            requiresAuth: true,
            requiredPermissions: ["report.stock_alert"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/damage-item",
        component: DamageItemReport,
        name: "reports/damage-item",
        meta: {
            title: "Report | Damage Item",
            requiresAuth: true,
            requiredPermissions: ["report.damage_item"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/income-statement",
        component: IncomeStatementReport,
        name: "reports/income-statement",
        meta: {
            title: "Report | Income Statement",
            requiresAuth: true,
            requiredPermissions: ["report.income_statement"],
        },
        beforeEnter: guard,
    },
    {
        path: "/reports/activity-log",
        component: ActivityLogReport,
        name: "reports/activity-log",
        meta: {
            title: "Report | Activity Log",
            requiresAuth: true,
            requiredPermissions: ["report.activity_log"],
        },
        beforeEnter: guard,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
});

export default router;
