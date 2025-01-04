<template>
  <div class="">
    <div class="row">
      <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
          <div class="card-header py-3">
            <h3 class="m-0 font-weight-bold" title="">Supplier Payments</h3>
            <p class="text-secondary m-0">Stores information about each supplier payment</p>
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
                  placeholder="Search by payment date, supplier name. (Type and Enter)"
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
                  to="/supplier_payments/create"
                  class="z-index-1 btn my-btn-primary float-right"
                >
                  <i class="fa fa-solid fa-plus"></i>
                  Add Payment
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
                    <!-- <th scope="col" class="text-center">
                      <input type="checkbox" class="form-check p-3" />
                    </th> -->
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
                    <th scope="col" class="text-nowrap">
                      <a href="#" @click.prevent="changeShort('payment_date')">
                        Payment Date</a
                      >
                      <span
                        v-if="
                          this.params.sort_field == 'title' &&
                          this.params.sort_direction == 'asc'
                        "
                        >↑</span
                      >
                      <span
                        v-if="
                          this.params.sort_field == 'title' &&
                          this.params.sort_direction == 'desc'
                        "
                        >↓</span
                      >
                    </th>
                    <th class="text-center text-nowrap" scope="col">Customer</th>
                    <th class="text-center text-nowrap" scope="col">
                      <a href="#" @click.prevent="changeShort('payment_amount')"
                        >Payment Amount</a
                      >
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
                    <th class="text-center text-nowrap" scope="col">Payment Method</th>
                   
                    <th class="text-center text-nowrap" scope="col">
                      <a href="#" @click.prevent="changeShort('paid_by')">Paid By</a>
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
                    <!-- <th>File</th> -->
                    <th class="text-center text-nowrap" scope="col">Action</th>
                  </tr>
                  <tr>
                    <!-- <th></th> -->
                    <th class="px-1">
                      <input
                        style="width: 70px"
                        type="text"
                        placeholder="By ID"
                        class="form-control-sm  p-0"
                        v-model="params.id"
                      />
                    </th>
                    <th colspan="1" class="text-nowarp px-1" style="min-width: 200px">
                      <input
                        type="text"
                        placeholder="Search By Date Range"
                        id="datecalander"
                        class="form-control-sm p-0"
                        style="width: 100%"
                        v-model="params.payment_date"
                      />
                    </th>
                    <th class="text-nowarp px-1" colspan="1" style="min-width: 200px">
                      <select
                        v-model="params.supplier_id"
                        style="width: 100%"
                        class="form-select p-0"
                      >
                        <option value="" selected>--select supplier--</option>
                        <option
                          :value="supplier.id"
                          v-for="supplier in suppliers"
                          :key="supplier.id"
                        >
                          {{ supplier.supplier_name }}
                        </option>
                      </select>
                    </th>
                    <th class="text-nowarp px-1">
                      <input
                        type="text"
                        placeholder="Search By Total Amount"
                        class="form-control-sm p-0"
                        style="width: 100%"
                        v-model="params.payment_amount"
                      />
                    </th>  
                    <th class="text-nowarp px-1" colspan="1" style="min-width: 200px">
                      <select
                        v-model="params.payment_method"
                        style="width: 100%"
                        class="form-select p-0"
                      >
                        <option value="" selected>--select payment method--</option>
                        <option
                          :value="payment_method.id"
                          v-for="payment_method in payment_methods"
                          :key="payment_method.id"
                        >
                          {{ payment_method.name }}
                        </option>
                      </select>
                    </th>               
                    <th></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody v-if="supplier_payments && paginator.totalRecords > 0">
                  <tr v-for="supplier_payment in supplier_payments.data" :key="supplier_payment.id">
                    <!-- <td class="text-center">
                      <input
                        type="checkbox"
                        :value="supplier_payment.id"
                        v-model="checked"
                        class="form-check-input"
                      />
                    </td> -->
                    <td style="width: 60px !important" class="text-nowrap">
                      {{ supplier_payment.id }}
                    </td>

                    <td class="text-nowrap">
                      <a
                        @click="openModal(supplier_payment.id)"
                        href="#"
                        data-toggle="modal"
                        data-target="#recordModal"
                        >{{ supplier_payment.payment_date }}</a
                      >
                    </td>
                    <td class="text-nowrap">{{ supplier_payment.supplier.supplier_name }}</td>
                    <td class="text-nowrap text-right">{{ supplier_payment.payment_amount }}</td>
                    <td class="text-nowrap">{{ supplier_payment.payment_method.name }}</td>
                    <td class="text-nowrap">{{ supplier_payment.paid_by }}</td>

                    <td class="text-right text-nowrap">
                      <div class="btn-group" option="group">
                       
                        <button
                          @click="openModal(supplier_payment.id)"
                          class="btn btn-sm my-btn-primary"
                          data-toggle="modal"
                          data-target="#recordModal"
                        >
                          <i class="fa fa-eye"></i> View
                        </button>
                        <!-- <router-link
                          :to="`/supplier_payments/${supplier_payment.id}`"
                          class="btn btn-sm my-btn-primary"
                          ><i class="fa fa-eye"></i> View</router-link
                        > -->
                        <router-link
                          :to="`/supplier_payments/${supplier_payment.id}/edit`"
                          class="btn btn-sm btn-primary px-2 mx-1"
                          ><i class="fa fa-edit"></i> Edit</router-link
                        >
                        <a
                          @click="deleteCustomerPayment(supplier_payment.id)"
                          class="btn btn-sm btn-danger disabled px-2 disabled"
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
                    <td colspan="7" class="text-center loading-section">
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
                  :data="supplier_payments"
                  :limit="5"
                  @pagination-change-page="getCustomerPayments"
                ></pagination>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--Row-->
    <!-- Bootstrap Modal -->
    <ViewSupplierPayment :record="record" :fileExtension="fileExtension" />
    <!-- Modal End -->
  </div>
</template>
<script type="text/javascript">
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";

import { mapActions } from "vuex";
import ViewSupplierPayment from "./ViewSupplierPayment.vue";
export default {
  name: "supplier_payment",
  components: { ViewSupplierPayment },
  data() {
    return {
      fileExtention: null,
      record: {},
      payment_methods: [],
      custmerPaymentPhotoUrl: null,
      publicPath: window.publicPath,
      checked: [],
      paginator: {
        totalRecords: 0,
        from: 0,
        to: 0,
        current_page: "",
        per_page: "",
      },
      supplier_payments: {
        type: Object,
        default: null,
      },
      params: {
        paginate: 5,
        id: "",
        payment_date: "",
        supplier_id: "",
        payment_amount: "",
        payment_method: "",
        sort_field: "created_at",
        sort_direction: "desc",
      },
      search: "",
      isLoading: false,
      isRefreshing: false,
      filterFields: {},
    };
  },
  async created() {
    // this.fetchCategories();
    this.suppliers = this.$store.getters.getCustomers;
    if (this.suppliers.length == 0) {
      const response = await axios.get("/api/get-suppliers");
      this.suppliers = response.data;
    }
    this.payment_methods = this.$store.getters.getPaymentMethods;
    if (this.payment_methods.length == 0) {
      const response = await axios.get("/api/get-payment-methods");
      this.payment_methods = response.data;
    }
  },
  mounted() {
    this.filterFields = { ...this.params };
    this.getCustomerPayments();
    flatpickr("#datecalander", {
      mode: "range",
      dateFormat: "Y-m-d", // Customize the date format as needed
      // Add more Flatpickr options as needed
    });
  },
  watch: {
    params: {
      handler() {
        this.getCustomerPayments();
      },
      deep: true,
    },
    search(val, old) {
      if (val.length >= 3 || old.length >= 3) {
        this.getCustomerPayments();
      }
    },
  },
  computed: {
  
  },
  methods: {
    ...mapActions(["fetchCategories"]),
    async getCustomerPayments(page = 1) {
      this.isLoading = true;
      await axios
        // .get(`/api/products?page=${page}`)
        // .get(`/api/products?page=${page}&supplier_payment_id=${this.params.supplier_payment_id}&sort_field=${this.params.sort_field}&sort_direction=${this.params.sort_direction}`)
        .get("/api/supplier-payments", {
          params: {
            page,
            search: this.search.length >= 3 ? this.search : "",
            ...this.params,
          },
        })
        .then((response) => {
          console.log(response.data);
          this.isLoading = false;
          this.supplier_payments = response.data;
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
      this.getCustomerPayments();
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
    deleteCustomerPayment(id) {
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
            .delete("/api/supplier-payments/" + id)
            .then(() => {
              this.getCustomerPayments();
              Notification.success("Data has been deleted successfully.");
            })
            .catch((error) => {
              // console.log(error);
              if (error.response.status === 422) {
                this.errors = error.response.data.errors;
                Notification.error(error.response.statusText);
              } else if (error.response.status === 401) {
                // statusText = "Unsaleized";
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
        '<span class="spinner-border spinner-border-sm" supplier_payment="status" aria-hidden="true" ></span> Exporting...';
      document.querySelector(".export-btn").innerHTML = loader;
      try {
        axios
          // .get("/api/products-export")
          .get("/api/supplier_payment-export", { responseType: "arraybuffer" })
          .then((response) => {
            if (response.status == 200) {
              document.querySelector(".export-btn").innerText = "Export to Excel";
              Notification.success("Exported Successfully");
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement("a");
              fileLink.href = fileURL;
              fileLink.setAttribute("download", "supplier_payment_list.xlsx");
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
        '<span class="spinner-border spinner-border-sm" supplier_payment="status" aria-hidden="true" ></span>  Exporting...PDF';
      document.querySelector(".export-btn-pdf").innerHTML = loader;
      axios.get("/api/supplier_payment-export-pdf", { responseType: "blob" }).then((response) => {
        document.querySelector(".export-btn-pdf").innerText = "Export PDF";
        Notification.success("Exported Successfully");
        var fileURL = window.URL.createObjectURL(
          new Blob([response.data], { type: "application/pdf" })
        );
        var fileLink = document.createElement("a");
        fileLink.href = fileURL;
        fileLink.setAttribute("download", "supplier_payment_list.pdf");
        document.body.appendChild(fileLink);
        fileLink.click();
      });
    },

    openModal(id) {
      // Fetch the record details from the server using Axios or a similar library
      axios
        .get(`api/supplier-payments/${id}`)
        .then((response) => {
          this.record = response.data;
          const fileName = response.data.supplier_payment.attach_file;
          const parts = fileName.split(".");
          if (parts.length > 1) {
            // Get the last part as the file extension
            this.fileExtension = parts[parts.length - 1].toLowerCase();
            // alert(this.fileExtension);
          } else {
            this.fileExtension = null; // No file extension found
          }
        
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
