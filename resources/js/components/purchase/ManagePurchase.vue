<template>
  <div class="">
    <div class="row">
      <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
          <div class="card-header py-3">
            <h3 class="m-0 font-weight-bold" title="">Purchase List</h3>
            <p class="text-secondary m-0">Stores information about each purchase</p>
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
                  placeholder="Search by purchase date, supplier name. (Type and Enter)"
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
                  to="/purchases/create"
                  class="z-index-1 btn my-btn-primary float-right"
                >
                  <i class="fa fa-solid fa-plus"></i>
                  Add purchase
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
                      <a href="#" @click.prevent="changeShort('purchase_date')">
                        Purchase Date</a
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
                    <th class="text-center text-nowrap" scope="col">Supplier</th>
                    <th class="text-center text-nowrap" scope="col">
                      <a href="#" @click.prevent="changeShort('total_amount')"
                        >Total Amount</a
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
                    <th class="text-center text-nowrap" scope="col">
                      <a href="#" @click.prevent="changeShort('discount_percentage')"
                        >Discount Percentage</a
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
                    <th class="text-center text-nowrap" scope="col">
                      <a href="#" @click.prevent="changeShort('discount_amount')"
                        >Discount Amount</a
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
                    <th class="text-center text-nowrap" scope="col">
                      <a href="#" @click.prevent="changeShort('vat_percentage')"
                        >Vat Percentage</a
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
                    <th class="text-center text-nowrap" scope="col">
                      <a href="#" @click.prevent="changeShort('vat_amount')"
                        >Vat Amount</a
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
                    <th class="text-center text-nowrap" scope="col">
                      <a href="#" @click.prevent="changeShort('net_amount')"
                        >Net Amount</a
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
                    <th class="text-center text-nowrap" scope="col">
                      <a href="#" @click.prevent="changeShort('pay_amount')"
                        >Pay Amount</a
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
                    <th class="text-center text-nowrap" scope="col">
                      <a href="#" @click.prevent="changeShort('due_amount')"
                        >Due Amount</a
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
                        class="form-control-sm"
                        v-model="params.id"
                      />
                    </th>
                    <th colspan="1" class="text-nowarp px-1" style="min-width: 200px">
                      <input
                        type="text"
                        placeholder="Search By Date Range"
                        id="datecalander"
                        class="form-control-sm"
                        style="width: 100%"
                        v-model="params.purchase_date"
                      />
                    </th>
                    <th class="text-nowarp px-1" colspan="1" style="min-width: 200px">
                      <select
                        v-model="params.supplier_id"
                        style="width: 100%"
                        class="form-select-sm"
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
                        class="form-control-sm"
                        style="width: 100%"
                        v-model="params.total_amount"
                      />
                    </th>

                    <th>
                      <input
                        type="text"
                        placeholder="Search By Discount Pecentage"
                        class="form-control-sm"
                        style="width: 100%"
                        v-model="params.discount_percentage"
                      />
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody v-if="purchases && paginator.totalRecords > 0">
                  <tr v-for="purchase in purchases.data" :key="purchase.id">
                    <!-- <td class="text-center">
                      <input
                        type="checkbox"
                        :value="purchase.id"
                        v-model="checked"
                        class="form-check-input"
                      />
                    </td> -->
                    <td style="width: 60px !important" class="text-nowrap">
                      {{ purchase.id }}
                    </td>

                    <td class="text-nowrap">
                      <a
                        @click="openModal(purchase.id)"
                        href="#"
                        data-toggle="modal"
                        data-target="#recordModal"
                        >{{ purchase.purchase_date }}</a
                      >
                    </td>
                    <td class="text-nowrap">{{ purchase.supplier.supplier_name }}</td>
                    <td class="text-nowrap text-right">{{ purchase.total_amount }}</td>
                    <td class="text-nowrap text-center">{{ purchase.discount_percentage }}</td>
                    <td class="text-nowrap text-right">{{ purchase.discount_amount }}</td>
                    <td class="text-nowrap text-center">{{ purchase.vat_percentage }}</td>
                    <td class="text-nowrap text-right">{{ purchase.vat_amount }}</td>
                    <td class="text-nowrap text-right">{{ purchase.net_amount }}</td>
                    <td class="text-nowrap text-right">{{ purchase.pay_amount }}</td>
                    <td class="text-nowrap text-right">{{ purchase.due_amount }}</td>
                    <td class="text-nowrap">{{ purchase.paid_by }}</td>

                    <td class="text-right text-nowrap">
                      <div class="btn-group" option="group">
                        <button
                          @click="openModal(purchase.id)"
                          class="btn btn-sm my-btn-primary"
                          data-toggle="modal"
                          data-target="#recordModal"
                        >
                          <i class="fa fa-eye"></i> View
                        </button>
                        <!-- <router-link
                          :to="`/purchases/${purchase.id}`"
                          class="btn btn-sm my-btn-primary"
                          ><i class="fa fa-eye"></i> View</router-link
                        > -->
                        <router-link
                          :to="`/purchases/${purchase.id}/edit`"
                          class="btn btn-sm btn-primary px-2 mx-1"
                          ><i class="fa fa-edit"></i> Edit</router-link
                        >
                        <a
                          @click="deletepurchase(purchase.id)"
                          class="btn btn-sm btn-danger px-2 disabled"
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
                  :data="purchases"
                  :limit="5"
                  @pagination-change-page="getPurchases"
                ></pagination>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--Row-->
    <!-- Bootstrap Modal -->
    <ViewPurchase :record="record" :fileExtension="fileExtension" />
    <!-- Modal End -->
  </div>
</template>
<script type="text/javascript">
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";

import { mapActions } from "vuex";
import ViewPurchase from "./ViewPurchase.vue";
export default {
  name: "purchase",
  components: { ViewPurchase },
  data() {
    return {
      fileExtention: null,
      record: {},
      sub_categories: [],
      purchasePhotoUrl: null,
      publicPath: window.publicPath,
      checked: [],
      paginator: {
        totalRecords: 0,
        from: 0,
        to: 0,
        current_page: "",
        per_page: "",
      },
      purchases: {
        type: Object,
        default: null,
      },
      params: {
        paginate: 12,
        id: "",
        purchase_date: "",
        supplier_id: "",
        total_amount: "",
        discount_percentage: "",
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
    this.suppliers = this.$store.getters.getSuppliers;
    if (this.suppliers.length == 0) {
      const response = await axios.get("/api/get-suppliers");
      this.suppliers = response.data;
    }
  },
  mounted() {
    this.filterFields = { ...this.params };
    this.getPurchases();
    flatpickr("#datecalander", {
      mode: "range",
      dateFormat: "Y-m-d", // Customize the date format as needed
      // Add more Flatpickr options as needed
    });
  },
  watch: {
    params: {
      handler() {
        this.getPurchases();
      },
      deep: true,
    },
    search(val, old) {
      if (val.length >= 3 || old.length >= 3) {
        this.getPurchases();
      }
    },
  },
  computed: {
    categories() {
      return this.$store.state.categories;
    },
  },
  methods: {
    ...mapActions(["fetchCategories"]),
    async getPurchases(page = 1) {
      this.isLoading = true;
      await axios
        // .get(`/api/products?page=${page}`)
        // .get(`/api/products?page=${page}&purchase_id=${this.params.purchase_id}&sort_field=${this.params.sort_field}&sort_direction=${this.params.sort_direction}`)
        .get("/api/purchases", {
          params: {
            page,
            search: this.search.length >= 3 ? this.search : "",
            ...this.params,
          },
        })
        .then((response) => {
          console.log(response.data);
          this.isLoading = false;
          this.purchases = response.data;
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
    getSubCategories() {
      axios
        .get("/api/get-category-wise-sub-categories", {
          params: {
            category_id: this.params.category_id,
          },
        })
        .then((response) => {
          this.sub_categories = response.data;
        });
    },
    refreshData() {
      this.isRefreshing = true;
      this.params = { ...this.filterFields };
      this.getPurchases();
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
    deletepurchase(id) {
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
            .delete("/api/purchases/" + id)
            .then(() => {
              this.getPurchases();
              Notification.success("Data has been deleted successfully.");
            })
            .catch((error) => {
              // console.log(error);
              if (error.response.status === 422) {
                this.errors = error.response.data.errors;
                Notification.error(error.response.statusText);
              } else if (error.response.status === 401) {
                // statusText = "Unpurchaseized";
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
        '<span class="spinner-border spinner-border-sm" purchase="status" aria-hidden="true" ></span> Exporting...';
      document.querySelector(".export-btn").innerHTML = loader;
      try {
        axios
          // .get("/api/products-export")
          .get("/api/purchase-export", { responseType: "arraybuffer" })
          .then((response) => {
            if (response.status == 200) {
              document.querySelector(".export-btn").innerText = "Export to Excel";
              Notification.success("Exported Successfully");
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement("a");
              fileLink.href = fileURL;
              fileLink.setAttribute("download", "purchase_list.xlsx");
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
        '<span class="spinner-border spinner-border-sm" purchase="status" aria-hidden="true" ></span>  Exporting...PDF';
      document.querySelector(".export-btn-pdf").innerHTML = loader;
      axios.get("/api/purchase-export-pdf", { responseType: "blob" }).then((response) => {
        document.querySelector(".export-btn-pdf").innerText = "Export PDF";
        Notification.success("Exported Successfully");
        var fileURL = window.URL.createObjectURL(
          new Blob([response.data], { type: "application/pdf" })
        );
        var fileLink = document.createElement("a");
        fileLink.href = fileURL;
        fileLink.setAttribute("download", "purchase_list.pdf");
        document.body.appendChild(fileLink);
        fileLink.click();
      });
    },
    openModal(id) {
      // Fetch the record details from the server using Axios or a similar library
      axios
        .get(`api/purchases/${id}`)
        .then((response) => {
          this.record = response.data;
          const fileName = response.data.purchase.attach_file;
		  if(fileName){
			  const parts = fileName.split(".");
			  if (parts.length > 1) {
				// Get the last part as the file extension
				this.fileExtension = parts[parts.length - 1].toLowerCase();
				// alert(this.fileExtension);
			  } else {
				this.fileExtension = null; // No file extension found
			  }
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
