<template>
  <div class="row">
    <div class="col-md-6 offset-md-3">
      <div class="card mt-3">
        <div class="card-header">
          <h3 class="text-center fw-bold">
            <i class="fa fa-chart-pie"></i> Category Wise Sale Report
          </h3>
        </div>
        <form id="form" @submit.prevent="submitForm">
          <div class="card-body">
            <div class="form-group">
              <label for=""
                >Category <span class="my-text-danger fw-bold"> *</span></label
              >
              <select
                name=""
                id=""
                class="form-select"
                v-model="form.category_id"
                @change="selectedCategory"
                :class="{ 'is-invalid': form.errors.has('category_id') }"
              >
                <option value="">--select category--</option>
                <option value="all">All Category</option>
                <option :value="category.id" v-for="category in categories">
                  {{ category.category_name }}
                </option>
              </select>
              <HasError :form="form" field="category_id" />
            </div>
            <div class="form-group">
              <label for=""
                >Publisher <span class="my-text-danger fw-bold"> *</span></label
              >
              <select
                name=""
                id=""
                class="form-select"
                v-model="form.publisher_id"
                @change="selectedPublisher"
                :class="{ 'is-invalid': form.errors.has('publisher_id') }"
              >
                <!-- <option value="">--select publisher--</option> -->
                <option value="all" selected>All Publisher</option>
                <option :value="publisher.id" v-for="publisher in publishers">
                  {{ publisher.publisher_name }}
                </option>
              </select>
              <HasError :form="form" field="publisher_id" />
            </div>
            <div class="form-group">
              <label for=""
                >Author <span class="my-text-danger fw-bold"> *</span></label
              >
              <select
                name=""
                id=""
                class="form-select"
                v-model="form.author_id"
                @change="selectedAuthor"
                :class="{ 'is-invalid': form.errors.has('author_id') }"
              >
                <!-- <option value="">--select author--</option> -->
                <option value="all" selected>All Author</option>
                <option :value="author.id" v-for="author in authors">
                  {{ author.author_name }}
                </option>
              </select>
              <HasError :form="form" field="author_id" />
            </div>
            <div class="form-group">
              <label for=""
                >Customer <span class="my-text-danger fw-bold"> *</span></label
              >
              <select
                name=""
                id=""
                class="form-select"
                v-model="form.customer_id"
                @change="selectCustomer"
                :class="{ 'is-invalid': form.errors.has('customer_id') }"
              >
                <!-- <option value="">--select customer--</option> -->
                <option value="all" selected>All Customer</option>
                <option :value="customer.id" v-for="customer in customers">
                  {{ customer.customer_name }}
                </option>
              </select>
              <HasError :form="form" field="customer_id" />
            </div>
            <div class="form-group">
              <label for=""
                >Date Range <span class="my-text-danger fw-bold"> *</span></label
              >
              <input
                type="text"
                v-model="form.date_range"
                placeholder="Search By Date Range"
                class="form-control datecalander"
                @input="clearError('date_range')"
                :class="{ 'is-invalid': form.errors.has('date_range') }"
              />
              <HasError :form="form" field="date_range" />
            </div>
          </div>
          <div class="card-footer">
            <div class="form-group text-center">
              <button
                type="submit"
                @click="getReport('view')"
                :disabled="isSubmitting"
                class="btn btn-md btn-secondary mx-1 export-btn-view"
              >
                <div><i class="fa fa-eye"> </i> View</div>
              </button>
              <button
                @click="getReport('excel')"
                type="submit"
                class="m-1 btn btn-md my-btn-success export-btn-excel"
                :disabled="isSubmitting"
              >
                <i class="fa fa-file-excel"> </i> Excel Export
              </button>
              <button
                @click="getReport('pdf')"
                type="submit"
                class="m-1 btn btn-md my-btn-danger export-btn-pdf"
                :disabled="isSubmitting"
              >
                <i class="fa fa-file-pdf"> </i> PDF Export
              </button>
              <button
                @click="getReport('print')"
                type="submit"
                class="m-1 btn btn-md my-btn-primary export-btn-print"
                :disabled="isSubmitting"
              >
                <i class="fa fa-print"></i> Print
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div ref="targetDiv" class="scroll-target">
    <hr />

    <div class="row" v-if="sales.length > 0">
      <div id="printableContent" class="col-md-10 offset-md-1 border my-3 rounded-sm">
        <h3 class="my-2">Sales</h3>
     
        <table class="table table-sm">
          <tr class="bg-secondary">
            <td class="fw-bold text-white text-center">Sale Date</td>
            <td class="fw-bold text-white text-left">Book Name</td>
            <td class="fw-bold text-white text-left">Author Name</td>
            <td class="fw-bold text-white text-left">Publisher Name</td>
            <td class="fw-bold text-white text-left">Customer Name</td>
            <td class="fw-bold text-white text-right">Quantity</td>
            <td class="fw-bold text-white text-right">Amount</td>
          </tr>
          <tr v-for="sale in sales">
            <td class="text-center">{{ sale.sale_date }}</td>
            <td class="text-left">{{ sale.book_name }}</td>
            <td class="text-left">{{ sale.author_name }}</td>
            <td class="text-left">{{ sale.publisher_name }}</td>
            <td class="text-left">{{ sale.customer_name }}</td>
            <td class="text-center">{{ sale.quantity }}</td>
            <td class="text-right">{{ sale.sub_total }}</td>
          </tr>
          <tr>
            <td
              class="fw-bold text-left" colspan="6"
            >
              Sale Total
            </td>
            <td class="fw-bold text-right">{{ calculateSubTotalAmount() }}</td>
          </tr>
        </table>
      </div>
    </div>
    <div v-else class="row my-3 text-center text-danger">
      <loader v-if="isSubmitting"></loader>
      <NoRecordFound v-else v-show="isResponsed" />
    </div>
  </div>
</template>
<script>
import axios from "axios";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";

import { mapActions } from "vuex";
export default {
  data() {
    return {
      serverStatus: null,
      isSubmitting: false,
      isResponsed: false,
      date_range: "",
      authors: [],
      publishers: [],
      customers: [],
      categories: [],
      sales: [],
      form: new Form({
        author_id:'all',
        publisher_id:'all',
        category_id: "",
        customer_id:'all',
        date_range: "",
        btn_type: null,
      }),
    };
  },
  async created() {
    this.fetchCategories();
    this.customers = this.$store.getters.getCustomers;
    if (this.customers.length == 0) {
      const response = await axios.get("/api/get-customers");
      this.customers = response.data;
    }
    this.authors = this.$store.getters.getAuthors;
    if (this.authors.length == 0) {
      const response = await axios.get("/api/get-authors");
      this.authors = response.data;
    }
    this.publishers = this.$store.getters.getPublishers;
    if (this.publishers.length == 0) {
      const response = await axios.get("/api/get-publishers");
      this.publishers = response.data;
    }
  },
  computed: {
    categories() {
      return this.$store.state.categories;
    },
  },
  mounted() {
    flatpickr(".datecalander", {
      mode: "range",
      dateFormat: "Y-m-d", // Customize the date format as needed
      // Add more Flatpickr options as needed
    });
  },
  methods: {
    ...mapActions(["fetchCategories"]),
    selectCustomer() {
      this.form.errors.clear("customer_id");
    },
    getReport(btnType) {
      this.form.btn_type = btnType;
      if (btnType == "view") {
        const targetDiv = this.$refs.targetDiv;
        targetDiv.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    },
    async submitForm() {
      if (!this.form.category_id) {
        this.form.errors.set("category_id", "Please fill out the category field.");
        return false;
      }
      if (!this.form.date_range) {
        this.form.errors.set("date_range", "Please fill out the date_range field.");
        return false;
      }
      // Disable the button when clicked

      this.isSubmitting = true;
      let resType = "";
      if (this.form.btn_type == "view") {
        let loader =
          '<span class="spinner-border spinner-border-sm" customer_payment="status" aria-hidden="true" ></span> Generating...';
        document.querySelector(".export-btn-view").innerHTML = loader;
      } else if (this.form.btn_type == "excel") {
        let loader =
          '<span class="spinner-border spinner-border-sm" customer_payment="status" aria-hidden="true" ></span> Exporting...';
        document.querySelector(".export-btn-excel").innerHTML = loader;
        resType = "arraybuffer";
      } else if (this.form.btn_type == "pdf") {
        let loader =
          '<span class="spinner-border spinner-border-sm" customer_payment="status" aria-hidden="true" ></span> Exporting...PDF';
        document.querySelector(".export-btn-pdf").innerHTML = loader;
        resType = "blob";
      } else if (this.form.btn_type == "print") {
        resType = "blob";
        let loader =
          '<span class="spinner-border spinner-border-sm" customer_payment="status" aria-hidden="true" ></span> Printing...';
        document.querySelector(".export-btn-print").innerHTML = loader;
      }
      await this.form
        .post("/api/report/category-wise-sale", {
          params: {
            ...this.form,
          },
          responseType: resType,
        })
        .then((response) => {
          if (response.data.report_type == "view") {
            this.isResponsed = true;
            this.date_range = response.data.date_range;
            this.customer = response.data.customer;
            this.sales = response.data.sales;
          } else {
            if (this.form.btn_type == "excel") {
              Notification.success("Exported Successfully");
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement("a");
              fileLink.href = fileURL;
              fileLink.setAttribute("download", "sale-report.xlsx");
              document.body.appendChild(fileLink);
              fileLink.click();
            } else if (this.form.btn_type == "pdf") {
              Notification.success("Exported Successfully");
              var fileURL = window.URL.createObjectURL(
                new Blob([response.data], { type: "application/pdf" })
              );
              var fileLink = document.createElement("a");
              fileLink.href = fileURL;
              fileLink.setAttribute("download", "customer_payment_list.pdf");
              document.body.appendChild(fileLink);
              fileLink.click();
            } else if (this.form.btn_type == "print") {
              document.querySelector(".export-btn-print").innerText = "Print";

              // Create a Blob from the binary data
              const blob = new Blob([response.data], { type: "application/pdf" });

              // Create a URL for the Blob
              const pdfUrl = window.URL.createObjectURL(blob);

              // Open the generated PDF in a new tab
              const newTab = window.open(pdfUrl, "_blank");

              // Print the PDF
              if (newTab) {
                // Programmatically trigger the print action after a timeout
                setTimeout(() => {
                  newTab.print();
                }, 500); // Adjust the delay as needed
              } else {
                // Handle if the new tab was blocked by the browser's pop-up blocker
                alert(
                  "The new tab was blocked by the browser's pop-up blocker. Please allow pop-ups and try again."
                );
              }
            }
          }
        })
        .catch((error) => {
          if (error.response.status === 422) {
            this.errors = error.response.data;
            Notification.error("Validation Errors!" + this.errors);
          } else if (error.response.status === 401) {
            this.errors = {};
            Notification.error(error.response.data.error);
          } else {
            Notification.error(error.response.statusText);
          }
        })
        .finally(() => {
          this.isSubmitting = false;
          if (this.form.btn_type == "excel") {
            document.querySelector(".export-btn-excel").innerHTML =
              '<i class="fa fa-file-excel"> </i> Excel Export';
          } else if (this.form.btn_type == "pdf") {
            document.querySelector(".export-btn-pdf").innerHTML =
              '<i class="fa fa-file-pdf"> </i> PDF Export';
          } else if (this.form.btn_type == "print") {
            document.querySelector(".export-btn-print").innerHTML =
              '<i class="fa fa-print"></i> Print';
          } else if (this.form.btn_type == "view") {
            document.querySelector(".export-btn-view").innerHTML =
              '<i class="fa fa-eye"> </i> View';
          }
        });
    },
    calculateSubTotalAmount() {
      return this.sales.reduce((total, sale) => total + Number(sale.sub_total), 0);
    },
    clearError(fieldName) {
      this.form.errors.clear(fieldName);
    },
  },
};
</script>
<style scoped>
.scroll-target {
  margin-top: 20px;
  /* Add more styles as needed */
}
</style>
