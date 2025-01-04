<template>
  <div>
    <div class="row">
      <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
          <div
            class="card-header py-3 d-flex flex-row align-items-center justify-content-between"
          >
            <h3 class="m-0 font-weight-bold">Permission List</h3>
          </div>
          <div class="card-body p-0 m-0">
            <!-- <div class="row p-2">
              <div class="col-md-6">
                <input
                  type="text"
                  v-model="searchTerm"
                  class="form-control"
                  style="width: 300px"
                  placeholder="Search Here"
                />
              </div>
              <div class="col-md-6">
                <router-link to="/add-permission" class="btn btn-primary float-right">
                  <i class="fa fa-solid fa-plus"></i>
                  Add permission
                </router-link>
              </div>
            </div> -->
            <!-- <div class="row justify-content-between"> -->
            <div class="row p-2">
              <div class="input-group">
                <div class="col-md-2">
                  <label for="" class="me-3">Per Page: </label>
                  <select v-model="params.paginate" id="" class="py-2">
                    <option value="5" >5</option>
                    <option value="10" selected>10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                  </select>
                </div>
                <input
                  type="text"
                  class="form-control"
                  placeholder="Search by permission. (Type and Enter)"
                  v-model="search"
                />
                <button @click="downloadFile" class="btn my-btn-success export-btn">
                  Export to Excel
                </button>
                <button @click="exportPdf" class="btn my-btn-danger export-btn-pdf">
                  Export PDF
                </button>
                <refresh-button
                  :is-refreshing="isRefreshing"
                  @refresh-data="refreshData"
                />

                <router-link
                  to="/add-permission"
                  class="z-index-1 btn my-btn-primary float-right"
                >
                  <i class="fa fa-solid fa-plus"></i>
                  Add Permission
                </router-link>
              </div>
            </div>

            <div class="table-responsive">
              <table
                class="table table-sm align-items-center table-flush"
                style="min-height: 250px"
              >
                <thead class="thead-light">
                  <tr>
                    <th scope="col" class="text-center">
                      <input type="checkbox" class="form-check p-3" />
                    </th>
                    <th scope="col">
                      <a href="#" @click.prevent="changeShort('id')">#ID</a>
                      <span
                        v-if="
                          this.params.sort_field == 'id' &&
                          this.params.sort_direction == 'asc'
                        "
                        >↑</span
                      >
                      <span
                        v-if="
                          this.params.sort_field == 'id' &&
                          this.params.sort_direction == 'desc'
                        "
                        >↓</span
                      >
                    </th>
                    <th scope="col">
                      <a href="#" @click.prevent="changeShort('name')">Name</a>
                      <!-- <a href="#">Name</a> -->
                      <span
                        v-if="
                          this.params.sort_field == 'name' &&
                          this.params.sort_direction == 'asc'
                        "
                        >↑</span
                      >
                      <span
                        v-if="
                          this.params.sort_field == 'name' &&
                          this.params.sort_direction == 'desc'
                        "
                        >↓</span
                      >
                    </th>
                    <th class="text-left">Permission Group</th>
                    <th class="text-center">Action</th>
                  </tr>
                  <tr>
                    <th></th>
                    <th>
                      <input
                        type="text"
                        placeholder="Search By ID"
                        class="form-control"
                        v-model="params.id"
                      />
                    </th>
                    <th>
                      <input
                        type="text"
                        placeholder="Search By Name"
                        class="form-control"
                        v-model="params.name"
                      />
                    </th>
                    <th>
                      <select v-model="params.group_name" class="form-select">
                        <option value="">--select group--</option>
                        <option value="user">User</option>
                        <option value="role">Role</option>
                        <option value="permission">Permission</option>
                        <option value="category">Category</option>
                        <option value="sub_category">Sub Category</option>
                        <option value="option_group">Option Group</option>
                        <option value="option">Option</option>
                        <option value="author">Author</option>
                        <option value="publisher">Publisher</option>
                        <option value="customer">Customer</option>
                        <option value="supplier">Supplier</option>
                        <option value="book">Book</option>
                        <option value="sale">Sale</option>
                        <option value="purchase">Purchase</option>
                        <option value="sale_return">Sale Return</option>
                        <option value="purchase_return">Purchase Return</option>
                        <option value="customer_payment">Customer Payment</option>
                        <option value="supplier_payment">Supplier Payment</option>
                        <option value="damage_item">Damage Item</option>
                        <option value="report">Report</option>
                        <option value="setting">Setting</option>
                      </select>
                    </th>
                    <th></th>
                  </tr>
                </thead>
                <tbody v-if="permissions && paginator.totalRecords > 0">
                  <tr v-for="permission in permissions.data" :key="permission.id">
                    <td class="text-center">
                      <input
                        type="checkbox"
                        :value="permission.id"
                        v-model="checked"
                        class="form-check-input"
                      />
                    </td>
                    <td class="text-nowrap">{{ permission.id }}</td>
                    <td>{{ permission.name }}</td>
                    <td>{{ permission.group_name }}</td>
                    <td class="text-right">
                      <router-link
                        :to="{ name: 'edit-permission', params: { id: permission.id } }"
                        class="btn btn-sm btn-primary px-2 mx-1"
                        ><i class="fa fa-edit"></i> Edit</router-link
                      >
                      <a
                        @click="deletePermission(permission.id)"
                        class="btn btn-sm btn-danger disabled px-2 mx-1"
                      >
                        <font color="#ffffff"><i class="fa fa-trash"></i> Delete</font></a
                      >
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <td
                      colspan="5"
                      class="text-center text-danger fw-bold py-5"
                      id="loading-section"
                    >
                      <loader v-if="isLoading"></loader>
                      <NoRecordFound v-else />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <div class="row">
              <div class="col-md-6">
                <p>
                  Current Page: {{ paginator.current_page }} Per Page:
                  {{ paginator.per_page }}, Showing {{ paginator.from }} to
                  {{ paginator.to }} of {{ paginator.totalRecords }} entries
                </p>
              </div>
              <div class="col-md-6">
                <pagination
                  align="right"
                  :data="permissions"
                  :limit="5"
                  @pagination-change-page="getPermissions"
                ></pagination>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--Row-->
  </div>
</template>
<script type="text/javascript">
export default {
  name: "permission",
  data() {
    return {
      checked: [],
      paginator: {
        totalRecords: 0,
        from: 0,
        to: 0,
        current_page: "",
        per_page: "",
      },
      permissions: {
        type: Object,
        default: null,
      },
      params: {
        paginate: 10,
        id: "",
        name: "",
        group_name: "",
        sort_field: "created_at",
        sort_direction: "desc",
      },
      search: "",
      isLoading: false,
      isRefreshing: false,
      filterFields: {},
    };
  },
  created() {
    if (!User.loggedIn()) {
      this.$router.push("/");
    }
  },
  mounted() {
    this.filterFields = { ...this.params };
    this.getPermissions();
  },
  watch: {
    params: {
      handler() {
        this.getPermissions();
      },
      deep: true,
    },
    search(val, old) {
      if (val.length >= 3 || old.length >= 3) {
        this.getPermissions();
      }
    },
  },
  computed: {},
  methods: {
    async getPermissions(page = 1) {
      this.isLoading = true;
      await axios
        // .get(`/api/products?page=${page}`)
        // .get(`/api/products?page=${page}&permission_id=${this.params.permission_id}&sort_field=${this.params.sort_field}&sort_direction=${this.params.sort_direction}`)
        .get("/api/manage-permission", {
          params: {
            page,
            search: this.search.length >= 3 ? this.search : "",
            ...this.params,
          },
        })
        .then((response) => {
          // console.log(response);
          this.permissions = response.data;
          this.paginator.totalRecords = response.data.total;
          // if (response.data.total <= 0) {
          //   document.getElementById("loading-section").innerText = "No Record Found!";
          // }
          this.paginator.from = response.data.from;
          this.paginator.to = response.data.to;
          this.paginator.current_page = response.data.current_page;
          this.paginator.per_page = response.data.per_page;
          this.isRefreshing = false;
        })
        .catch((error) => {
          this.isRefreshing = false;
          if (error.response.status == 403) {
            Notification.error(error.response.data.message);
            // document.getElementById("loading-section").innerHtml = `<h3>${error.response.data.message}</h3>`;
          } else {
            Notification.error(error.response.data.error);
          }
          //  document.getElementById("loading-section").innerText = error.response.data.error;
        })
        .finally(() => {
          // always executed;
          this.isLoading = false;
        });
    },
    refreshData() {
      this.isRefreshing = true;
      this.params = { ...this.filterFields };
      this.getPermissions();
    },
    changeShort(field) {
      if (this.params.sort_field === field) {
        this.params.sort_direction =
          this.params.sort_direction === "asc" ? "desc" : "asc";
      } else {
        this.params.sort_field = field;
        this.params.sort_direction = "asc";
      }
      // this.getProducts();
    },
    deletePermission(id) {
      Swal.fire({
        allowOutsideClick: false,
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.value) {
          axios
            .delete("/api/manage-permission/" + id)
            .then(() => {
              this.getPermissions();
              Notification.success("Data has been deleted successfully.");
            })
            .catch((error) => {
              // console.log(error);
              if (error.response.status === 422) {
                this.errors = error.response.data.errors;
                Notification.error(error.response.statusText);
              } else if (error.response.status === 401) {
                // statusText = "Unauthorized";
                this.errors = {};
                Notification.error(error.response.data.error);
              } else {
                Notification.error(error.response.statusText);
              }
            });
        }
      });
    },
    downloadFile() {
      let loader =
        '<span class="spinner-border spinner-border-sm" permission="status" aria-hidden="true" ></span> Exporting...';
      document.querySelector(".export-btn").innerHTML = loader;
      try {
        axios
          // .get("/api/products-export")
          .get("/api/permission-export", { responseType: "arraybuffer" })
          .then((response) => {
            if (response.status == 200) {
              document.querySelector(".export-btn").innerText = "Export to Excel";
              Notification.success("Exported Successfully");
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement("a");
              fileLink.href = fileURL;
              fileLink.setAttribute("download", "product_list.xlsx");
              document.body.appendChild(fileLink);
              fileLink.click();
            } else {
              this.$swal("ERROR!", `${response.data.message}`, "error");
            }
          });
      } catch (error) {
        this.$swal("ERROR!", `${error}`, "error");
        // console.error(error);
      }
    },
    exportPdf() {
      let loader =
        '<span class="spinner-border spinner-border-sm" permission="status" aria-hidden="true" ></span>  Exporting...PDF';
      document.querySelector(".export-btn-pdf").innerHTML = loader;
      axios
        .get("/api/permission-export-pdf", { responseType: "blob" })
        .then((response) => {
          document.querySelector(".export-btn-pdf").innerText = "Export PDF";
          Notification.success("Exported Successfully");
          var fileURL = window.URL.createObjectURL(
            new Blob([response.data], { type: "application/pdf" })
          );
          var fileLink = document.createElement("a");
          fileLink.href = fileURL;
          fileLink.setAttribute("download", "permission_list.pdf");
          document.body.appendChild(fileLink);
          fileLink.click();
        });
    },
  },
};
</script>
<style type="text/css">
#em_photo {
  height: 40px;
  width: 40px;
}
</style>
