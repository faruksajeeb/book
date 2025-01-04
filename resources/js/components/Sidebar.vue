<template lang="">
    <div >
        <ul
      class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
      <router-link to="/dashboard" class="sidebar-brand d-flex justify-content-center bg-white">
        <div class="sidebar-brand-icon">
          <img :src="`${publicPath}assets/img/logo/logo.png`" width="200"  >
        </div>
        <!-- <div class="sidebar-brand-text mx-3">eShop</div> -->
      </router-link>
      
      <hr class="sidebar-divider my-0">
      <!-- <li class="nav-item active">
        <router-link class="nav-link" to="/dashboard">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span></router-link>
      </li> -->
      
     <li class="nav-item my-bg-primary">
        <router-link class="nav-link  text-light" to="/sales/create">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span >POS</span></router-link>
      </li>
      <hr class="sidebar-divider my-2">
      <div style="max-height: 500px; overflow-y: auto;">
        
      <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUserMgt"
            aria-expanded="true" aria-controls="collapseUserMgt">
            <i class="far fa-fw fa-folder-open"></i>
            <span>User Management</span>
          </a>
          <div id="collapseUserMgt" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
            <ul id="accordionUserMgtBar" class="navbar-nav accordion px-3" >
            <li class="nav-item py-2 ">
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapseUserRole"
                      aria-expanded="true" aria-controls="collapseUserRole">                
                      <span>Roles</span>
                    </a>
                    <div id="collapseUserRole" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionUserMgtBar">
                      <div class="bg-white py-2  rounded">
                        <router-link v-if="hasPermission('role.create')"  class="collapse-item p-1" to="/roles/create">Add Role</router-link>
                        <router-link v-if="hasPermission('role.manage')"  class="collapse-item p-1" to="/roles">Manage Role</router-link>
                      </div>
                  </div>
            </li>
            <li class="nav-item py-2">
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapseUserLevel"
                      aria-expanded="true" aria-controls="collapseUserLevel">                   
                      <span>Users</span>
                    </a>
                    <div id="collapseUserLevel" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionUserMgtBar">
                      <div class="bg-white py-2  rounded">
                        <router-link v-if="hasPermission('user.create')" class="collapse-item p-1" to="/users/create">Add User</router-link>
                      
                        <router-link v-if="hasPermission('user.manage')"   class="collapse-item p-1" to="/users">Manage User</router-link>
                      
                      </div>
                  </div>
            </li>
            <li class="nav-item py-2"  v-if="user_roles.includes('developer')">
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapseUserPermission"
                      aria-expanded="true" aria-controls="collapseUserPermission">                   
                      <span>Permissions</span>
                    </a>
                    <div id="collapseUserPermission" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionUserMgtBar">
                      <div class="bg-white py-2  rounded">
                        <router-link v-if="hasPermission('permission.create')"  class="collapse-item p-1" to="/add-permission">Add Permission</router-link>
                        <router-link v-if="hasPermission('permission.manage')"  class="collapse-item p-1" to="/manage-permission">Manage Permission</router-link>
                      </div>
                  </div>
            </li>
            </ul>           
            </div>
          </div>
      </li>
      <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMatserDataMgt"
            aria-expanded="true" aria-controls="collapseMatserDataMgt">
            <i class="far fa-fw fa-folder-open"></i>
            <span>Master Data</span>
          </a>
          <div id="collapseMatserDataMgt" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
            <ul id="accordionMasterDataMgtBar" class="navbar-nav accordion px-3" >
              <li class="nav-item py-2 " v-if="user_roles.includes('developer')">
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapseOptionGroup"
                      aria-expanded="true" aria-controls="collapseOptionGroup">                
                      <span>Option Group</span>
                    </a>
                    <div id="collapseOptionGroup" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionMasterDataMgtBar">
                      <div class="bg-white py-2  rounded">
                        <router-link v-if="hasPermission('option_group.create')"  class="collapse-item p-1" to="/option-groups/create">Add Option Group</router-link>
                        <router-link v-if="hasPermission('option_group.manage')"  class="collapse-item p-1" to="/option-groups">Manage Option Group</router-link>
                      </div>
                  </div>
            </li>
            <li class="nav-item py-2 ">
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapseOption"
                      aria-expanded="true" aria-controls="collapseOption">                
                      <span>Option</span>
                    </a>
                    <div id="collapseOption" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionMasterDataMgtBar">
                      <div class="bg-white py-2  rounded">
                        <router-link v-if="hasPermission('option.create')"  class="collapse-item p-1" to="/options/create">Add Option</router-link>
                        <router-link v-if="hasPermission('option.manage')"  class="collapse-item p-1" to="/options">Manage Option</router-link>
                      </div>
                  </div>
            </li>
            <li class="nav-item py-2">
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapseCategory"
                      aria-expanded="true" aria-controls="collapseCategory">                   
                      <span>Category</span>
                    </a>
                    <div id="collapseCategory" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionMasterDataMgtBar">
                      <div class="bg-white py-2  rounded">
                        <router-link v-if="hasPermission('user.create')" class="collapse-item p-1" to="/categories/create">Add Category</router-link>                      
                        <router-link v-if="hasPermission('user.manage')"   class="collapse-item p-1" to="/categories">Manage Category</router-link>                      
                      </div>
                  </div>
            </li>
            <li class="nav-item py-2">
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapseSubCategory"
                      aria-expanded="true" aria-controls="collapseSubCategory">                   
                      <span>Sub-category</span>
                    </a>
                    <div id="collapseSubCategory" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionMasterDataMgtBar">
                      <div class="bg-white py-2  rounded">
                        <router-link v-if="hasPermission('sub_category.create')"  class="collapse-item p-1" to="/sub-categories/create">Add Sub-category</router-link>
                        <router-link v-if="hasPermission('sub_category.manage')"  class="collapse-item p-1" to="/sub-categories">Manage Sub-category</router-link>
                      </div>
                  </div>
            </li>
            <li class="nav-item py-2">
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapseAuthor"
                      aria-expanded="true" aria-controls="collapseAuthor">                   
                      <span>Authors</span>
                    </a>
                    <div id="collapseAuthor" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionMasterDataMgtBar">
                      <div class="bg-white py-2  rounded">
                        <router-link v-if="hasPermission('author.create')"  class="collapse-item p-1" to="/authors/create">Add Author</router-link>
                        <router-link v-if="hasPermission('author.manage')"  class="collapse-item p-1" to="/authors">Manage Author</router-link>
                      </div>
                  </div>
            </li>
            <li class="nav-item py-2">
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapsePublisher"
                      aria-expanded="true" aria-controls="collapsePublisher">                   
                      <span>Publishers</span>
                    </a>
                    <div id="collapsePublisher" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionMasterDataMgtBar">
                      <div class="bg-white py-2  rounded">
                        <router-link v-if="hasPermission('publisher.create')"  class="collapse-item p-1" to="/publishers/create">Add Publisher</router-link>
                        <router-link v-if="hasPermission('publisher.manage')"  class="collapse-item p-1" to="/publishers">Manage Publisher</router-link>
                      </div>
                  </div>
            </li>
            </ul>           
            </div>
          </div>
      </li>
      
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCustomer"
          aria-expanded="true" aria-controls="collapseCustomer">
          <i class="fas fa-fw fa-users"></i>
          <span>Customers</span>
        </a>
        <div id="collapseCustomer" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <router-link v-if="hasPermission('customer.create')"  class="collapse-item" to="/customers/create">Add Customer</router-link>
            <router-link v-if="hasPermission('customer.manage')"  class="collapse-item" to="/customers">Manage Customer</router-link>
          </div>
        </div>
      </li>
      
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSupplier" aria-expanded="true"
          aria-controls="collapseSupplier">
          <i class="fas fa-fw fa-users"></i>
          <span>Suppliers</span>
        </a>
        <div id="collapseSupplier" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <router-link v-if="hasPermission('supplier.create')"  class="collapse-item" to="/suppliers/create">Add Supplier</router-link>
            <router-link v-if="hasPermission('supplier.manage')"  class="collapse-item" to="/suppliers">Manage Supplier</router-link>
          </div>
        </div>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseProduct"
          aria-expanded="true" aria-controls="collapseProduct">
          <i class="fas fa-solid fa-book"></i>
          <span>Books</span>
        </a>
        <div id="collapseProduct" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <router-link v-if="hasPermission('book.create')"  class="collapse-item" to="/books/create">Add Book</router-link>
            <router-link v-if="hasPermission('book.manage')"  class="collapse-item" to="/books">Manage Book</router-link>
          </div>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePurchase"
          aria-expanded="true" aria-controls="collapsePurchase">
          <i class="far fa-fw fa-window-maximize"></i>
          <span>Purchases</span>
        </a>
        <div id="collapsePurchase" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <router-link v-if="hasPermission('purchase.create')"  class="collapse-item" to="/purchases/create">Add Purchase</router-link>
            <router-link v-if="hasPermission('purchase.manage')"  class="collapse-item" to="/purchases">Manage Purchase</router-link>
          </div>
        </div>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSale"
          aria-expanded="true" aria-controls="collapseSale">
          <i class="fas fa-shopping-cart"></i>
          <span>Sales</span>
        </a>
        <div id="collapseSale" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <router-link v-if="hasPermission('sale.create')"  class="collapse-item" to="/sales/create">Add Sale</router-link>
            <router-link v-if="hasPermission('sale.manage')"  class="collapse-item" to="/sales">Manage Sale</router-link>
          </div>
        </div>
      </li>     

      <!-- <li class="nav-item">
        <router-link class="nav-link" to="/stock">
          <i class="fab fa-fw fa-wpforms"></i>
          <span>Stock</span>
        </router-link>
      </li> -->
      <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePayment"
            aria-expanded="true" aria-controls="collapsePayment">
            <i class="fas fa-dollar-sign"></i>
            <span>Payments</span>
          </a>
          <div id="collapsePayment" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
            <ul id="accordionPaymentBar" class="navbar-nav accordion px-3" >
            <li class="nav-item py-2 ">
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapseCustomerPayment"
                      aria-expanded="true" aria-controls="collapseCustomerPayment">                
                      <span>Customer Payments</span>
                    </a>
                    <div id="collapseCustomerPayment" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionPaymentBar">
                      <div class="bg-white py-2  rounded">
                        <router-link v-if="hasPermission('customer_payment.create')"  class="collapse-item p-1" to="/customer_payments/create">Add Payment</router-link>
                        <router-link v-if="hasPermission('customer_payment.manage')"  class="collapse-item p-1" to="/customer_payments">Manage Payment</router-link>
                      </div>
                  </div>
            </li>
            <li class="nav-item py-2">
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapseSupplierPayment"
                      aria-expanded="true" aria-controls="collapseSupplierPayment">                   
                      <span>Supplier Payments</span>
                    </a>
                    <div id="collapseSupplierPayment" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionPaymentBar">
                      <div class="bg-white py-2  rounded">
                        <router-link v-if="hasPermission('supplier_payment.create')" class="collapse-item p-1" to="/supplier_payments/create">Add Payment</router-link>                      
                        <router-link v-if="hasPermission('supplier_payment.manage')"   class="collapse-item p-1" to="/supplier_payments">Manage Payment</router-link>                      
                      </div>
                  </div>
            </li>
            </ul>           
            </div>
          </div>
      </li>
      <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReturn"
            aria-expanded="true" aria-controls="collapseReturn">
            <i class="fas fa-undo"></i>
            <span>Returns</span>
          </a>
          <div id="collapseReturn" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
            <ul id="accordionReturnBar" class="navbar-nav accordion px-3" >
            <li class="nav-item py-2 "  v-if="hasPermission('sale_return.create') || hasPermission('sale_return.manage')">
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapseSaleReturn"
                      aria-expanded="true" aria-controls="collapseSaleReturn">                
                      <span>Sale Returns</span>
                    </a>
                    <div id="collapseSaleReturn" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionReturnBar">
                      <div class="bg-white py-2  rounded">
                        <router-link class="collapse-item p-1" v-if="hasPermission('sale_return.create')" to="/sale-returns/create">Add Return</router-link>
                        <router-link class="collapse-item p-1" v-if="hasPermission('sale_return.manage')" to="/sale-returns">Manage Return</router-link>
                      </div>
                  </div>
            </li>
            <li class="nav-item py-2"  v-if="hasPermission('purchase_return.create') || hasPermission('purchase_return.manage')" >
                    <a class="nav-link-layer-two collapsed" href="#" data-toggle="collapse" data-target="#collapsePurchaseReturn"
                      aria-expanded="true" aria-controls="collapsePurchaseReturn">                   
                      <span>Purchase Returns</span>
                    </a>
                    <div id="collapsePurchaseReturn" class="collapse ms-0" aria-labelledby="headingBootstrap" data-parent="#accordionReturnBar">
                      <div class="bg-white py-2  rounded">
                        <router-link v-if="hasPermission('purchase_return.create')" class="collapse-item p-1" to="/purchase-returns/create">Add Return</router-link>                      
                        <router-link v-if="hasPermission('purchase_return.manage')"   class="collapse-item p-1" to="/purchase-returns">Manage Return</router-link>
                      
                      </div>
                  </div>
            </li>
            </ul>           
            </div>
          </div>
      </li>

 <li class="nav-item" v-if="hasPermission('damage_item.manage')" >
        <router-link  class="nav-link" to="/damage-items">
          <i class="fab fa-fw fa-wpforms"></i>
          <span>Damage Items</span>
        </router-link>
      </li>
      <li class="nav-item" v-if="hasPermission('report.view')" >
        <router-link  class="nav-link" to="/reports">
          <i class="fas fa-file-image"></i>
          <span>Reports</span>
        </router-link>
      </li>
<!--       
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseEmployee"
          aria-expanded="true" aria-controls="collapseEmployee">
          <i class="far fa-fw fa-window-maximize"></i>
          <span>Employees</span>
        </a>
        <div id="collapseEmployee" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <router-link class="collapse-item" to="/add-employee">Add Employee</router-link>
            <router-link class="collapse-item" to="/manage-employee">Manage Employee</router-link>
          </div>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseExpense"
          aria-expanded="true" aria-controls="collapseExpense">
          <i class="far fa-fw fa-window-maximize"></i>
          <span>Expense</span>
        </a>
        <div id="collapseExpense" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <router-link class="collapse-item" to="/add-expense">Add Expense</router-link>
            <router-link class="collapse-item" to="/manage-expense">Manage Expense</router-link>
          </div>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSalary"
          aria-expanded="true" aria-controls="collapseSalary">
          <i class="far fa-fw fa-window-maximize"></i>
          <span>Salary</span>
        </a>
        <div id="collapseSalary" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">

            <router-link class="collapse-item" to="/given-salary">Add Salary</router-link>
            <router-link class="collapse-item" to="/salary">Manage Salary</router-link>

          </div>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBootstrap51"
          aria-expanded="true" aria-controls="collapseBootstrap51">
          <i class="far fa-fw fa-window-maximize"></i>
          <span>Orders</span>
        </a>
        <div id="collapseBootstrap51" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
           
            <router-link class="collapse-item" to="/order">Today Order</router-link>
            <router-link class="collapse-item" to="/searchorder">Search</router-link>
            
          </div>
        </div>
      </li>
    

      <li class="nav-item">
        <a class="nav-link" href="forms.html">
          <i class="fab fa-fw fa-wpforms"></i>
          <span>Forms</span>
        </a>
      </li>
     
      <li class="nav-item">
        <a class="nav-link" href="ui-colors.html">
          <i class="fas fa-fw fa-palette"></i>
          <span>UI Colors</span>
        </a>
      </li> -->
      <!-- <hr class="sidebar-divider">
      <div class="sidebar-heading">
        Examples
      </div>
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePage" aria-expanded="true"
          aria-controls="collapsePage">
          <i class="fas fa-fw fa-columns"></i>
          <span>Pages</span>
        </a>
        <div id="collapsePage" class="collapse" aria-labelledby="headingPage" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Example Pages</h6>
            <a class="collapse-item" href="login.html">Login</a>
            <a class="collapse-item" href="register.html">Register</a>
            <a class="collapse-item" href="404.html">404 Page</a>
            <a class="collapse-item" href="blank.html">Blank Page</a>
          </div>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="charts.html">
          <i class="fas fa-fw fa-chart-area"></i>
          <span>Charts</span>
        </a>
      </li>
       -->
      
    </div>
      <hr class="sidebar-divider">
      <div class="version" id="version-ruangadmin"></div>
    </ul>
    
    </div>
</template>
<script>
import { mapActions } from 'vuex';
export default {
    name: 'Sidebar',

	data(){
	 return {	
    user_roles : User.userRoles().split(","), 
		publicPath: window.publicPath,
	 }
	},
  created(){
    
  },
    mounted() {
      this.fetchUserPermissions(); // Fetch user permissions when the component is mounted
    },
    computed: {
      userPermissions() { 
        // Fetch user permissions from Vuex store or component data
        const permissions = this.$store.state.user.permissions;
        return Array.isArray(permissions) ? permissions : Object.values(permissions);
      }
    },
    methods: {
      ...mapActions(['fetchUserPermissions']),
       // Helper method to check permissions
      hasPermission(permission) {
        return this.userPermissions.includes(permission);
      },
    }
}

</script>
<style lang="" scoped>

</style>
