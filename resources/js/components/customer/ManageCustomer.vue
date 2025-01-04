<template>
  <div>
    <div class="row">
      <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
          <div
            class="card-header py-3 d-flex flex-row align-items-center justify-content-between"
          >
            <h3 class="m-0 font-weight-bold">Customer List</h3>
          </div>
          <div class="card-body p-0 m-0">
            <div class="row p-2">
              <div class="input-group">
                <div class="col-md-2">
                  <label for="" class="me-3">Per Page: </label>
                  <select v-model="params.paginate" id="" class="py-2">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                  </select>
                </div>
                <input
                  type="text"
                  class="form-control"
                  placeholder="Search by Customer. (Type and Enter)"
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
                  to="/customers/create"
                  class="z-index-1 btn my-btn-primary float-right"
                >
                  <i class="fa fa-solid fa-plus"></i>
                  Add Customer
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
                    <th class="text-right">Photo</th>
                    <th scope="col">
                      <a href="#" @click.prevent="changeShort('customer_name')"
                        >Name</a
                      >
                      <!-- <a href="#">Name</a> -->
                      <span
                        v-if="
                          this.params.sort_field == 'customer_name' &&
                          this.params.sort_direction == 'asc'
                        "
                        >↑</span
                      >
                      <span
                        v-if="
                          this.params.sort_field == 'customer_name' &&
                          this.params.sort_direction == 'desc'
                        "
                        >↓</span
                      >
                    </th>
                    <th class="text-right"  title="Posetive=Due, (-)Negetive=Advanced">Balance</th>
                    <th class="text-left">Phone</th>
                    <th class="text-left">Email</th>
                    <th class="text-right">Address</th>
                    <th class="text-center">Action</th>
                  </tr>
                  <tr>
                    <th colspan="2" class="p-0 m-0 ">
                      <input
                        type="text"
                        placeholder="Search By ID"
                        class="form-control"
                        v-model="params.id"
                      />
                    </th>
                    <th colspan="2" class="p-0 m-0 ">
                      <input
                        type="text"
                        placeholder="Search By Name"
                        class="form-control"
                        v-model="params.customer_name"
                      />
                    </th>
                    <th colspan="2" class="p-0 m-0 ">
                      <input
                        type="text"
                        placeholder="Search By Mobile No"
                        class="form-control"
                        v-model="params.customer_phone"
                      />
                    </th>
                    <th class="p-0 m-0">
                      <input
                        type="text"
                        placeholder="Search By Customer Email"
                        class="form-control"
                        v-model="params.customer_email"
                      />
                    </th>
                    <th colspan="2" class="p-0 m-0">
                      <input
                        type="text"
                        placeholder="Search By Customer Address"
                        class="form-control"
                        v-model="params.customer_address"
                      />
                    </th>
                  </tr>
                </thead>
                <tbody v-if="customers && paginator.totalRecords > 0">
                  <tr v-for="customer in customers.data" :key="customer.id">
                    <td class="text-center">
                      <input
                        type="checkbox"
                        :value="customer.id"
                        v-model="checked"
                        class="form-check-input"
                      />
                    </td>
                    <td class="text-nowrap">{{ customer.id }}</td>
                    <td>
                      <img
                        :src="
                          `${publicPath}assets/img/customer/thumbnail/` +
                          customer.customer_photo
                        "
                        alt=""
                        width="50"
                      />
                    </td>
                    <td>{{ customer.customer_name }}</td>
                    <td>{{ customer.balance }}</td>
                    <td>{{ customer.customer_phone }}</td>
                    <td>{{ customer.customer_email }}</td>
                    <td>{{ customer.customer_address }}</td>

                    <td class="text-right text-nowrap">
                      <div class="btn-group" option="group">
                        <button @click="openModal(customer.id)" class="btn btn-sm my-btn-primary"  data-toggle="modal" data-target="#recordModal" > <i class="fa fa-eye"></i> View </button>
                        <!-- <router-link
                          :to="`/customers/${customer.id}`"
                          class="btn btn-sm my-btn-primary"
                          ><i class="fa fa-eye"></i> View</router-link
                        > -->
                        <router-link
                          :to="`/customers/${customer.id}/edit`"
                          class="btn btn-sm btn-primary px-2 mx-1"
                          ><i class="fa fa-edit"></i> Edit</router-link
                        >
                        <a
                          @click="deleteCustomer(customer.id)"
                          class="btn btn-sm btn-danger disabled px-2"
                        >
                          <font color="#ffffff"
                            ><i class="fa fa-trash"></i> Delete</font
                          ></a
                        >
                      </div>
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <td colspan="8" class="text-center loading-section">
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
                  :data="customers"
                  :limit="5"
                  @pagination-change-page="getCustomers"
                ></pagination>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--Row-->
    <!-- Bootstrap Modal -->
    <ViewCustomer :record="record" />
    <!-- Modal End -->
  </div>
</template>
<script type="text/javascript">
import ViewCustomer from './ViewCustomer.vue'
export default {
  name: "Customer",
  components:{ViewCustomer},
  data() {
    return {
      record: {},
      customerPhotoUrl: null,
      publicPath: window.publicPath,
      checked: [],
      paginator: {
        totalRecords: 0,
        from: 0,
        to: 0,
        current_page: "",
        per_page: "",
      },
      customers: {
        type: Object,
        default: null,
      },
      params: {
        paginate: 5,
        id: "",
        customer_name: "",
        category_id: "",
        sort_field: "created_at",
        sort_direction: "desc",
      },
      search: "",
      isLoading: false,
      isRefreshing: false,
      filterFields: {},
    };
  },
  create() {},
  mounted() {
    this.filterFields = { ...this.params };
    this.getCustomers();
  },
  watch: {
    params: {
      handler() {
        this.getCustomers();
      },
      deep: true,
    },
    search(val, old) {
      if (val.length >= 3 || old.length >= 3) {
        this.getCustomers();
      }
    },
  },
  computed: {
    categories() {
      return this.$store.state.categories;
    },
  },
  methods: {
    async getCustomers(page = 1) {
      this.isLoading = true;
      await axios
        // .get(`/api/products?page=${page}`)
        // .get(`/api/products?page=${page}&Customer_id=${this.params.Customer_id}&sort_field=${this.params.sort_field}&sort_direction=${this.params.sort_direction}`)
        .get("/api/customers", {
          params: {
            page,
            search: this.search.length >= 3 ? this.search : "",
            ...this.params,
          },
        })
        .then((response) => {
          // console.log(response);
          this.isLoading = false;
          this.customers = response.data;
          this.paginator.totalRecords = response.data.total;
          // if (response.data.total <= 0) {
          //   document.querySelector(".loading-section").innerText = "No Record Found!.";
          // }
          this.paginator.from = response.data.from;
          this.paginator.to = response.data.to;
          this.paginator.current_page = response.data.current_page;
          this.paginator.per_page = response.data.per_page;
          this.isRefreshing = false;
        })
        .catch((error) => {
          this.isLoading = false;
          document.querySelector(".loading-section").innerText =
            error.response.data.error;
            this.isRefreshing = false;
          if (error.response.status == 403) {
            Notification.error(error.response.data.message);
            // document.getElementById("loading-section").innerHtml = `<h3>${error.response.data.message}</h3>`;
          } else {
            Notification.error(error.response.data.error);
          }
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    refreshData() {
      this.isRefreshing = true;
      this.params = { ...this.filterFields };
      this.getCustomers();
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
    deleteCustomer(id) {
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
            .delete("/api/customers/" + id)
            .then(() => {
              this.getCustomers();
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
        '<span class="spinner-border spinner-border-sm" Customer="status" aria-hidden="true" ></span> Exporting...';
      document.querySelector(".export-btn").innerHTML = loader;
      try {
        axios
          // .get("/api/products-export")
          .get("/api/customer-export", { responseType: "arraybuffer" })
          .then((response) => {
            if (response.status == 200) {
              document.querySelector(".export-btn").innerText = "Export to Excel";
              Notification.success("Exported Successfully");
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement("a");
              fileLink.href = fileURL;
              fileLink.setAttribute("download", "customer_list.xlsx");
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
        '<span class="spinner-border spinner-border-sm" customer="status" aria-hidden="true" ></span>  Exporting...PDF';
      document.querySelector(".export-btn-pdf").innerHTML = loader;
      axios.get("/api/customer-export-pdf", { responseType: "blob" }).then((response) => {
        document.querySelector(".export-btn-pdf").innerText = "Export PDF";
        Notification.success("Exported Successfully");
        var fileURL = window.URL.createObjectURL(
          new Blob([response.data], { type: "application/pdf" })
        );
        var fileLink = document.createElement("a");
        fileLink.href = fileURL;
        fileLink.setAttribute("download", "customer_list.pdf");
        document.body.appendChild(fileLink);
        fileLink.click();
      });
    },
    openModal(id) {
      // Fetch the record details from the server using Axios or a similar library
      axios
        .get(`api/customers/${id}`)
        .then((response) => {
          this.record = response.data;
          // Open the Bootstrap modal
          // $("#recordModal").modal("show");
        })
        .catch((error) => {
          console.error(error);
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
