<template>
  <div class="">
    <div class="row">
      <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
          <div class="card-header py-3">
            <h3 class="m-0 font-weight-bold" title="">Damage Item List</h3>
            <p class="text-secondary m-0">Stores information about each damage entries</p>
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
                  placeholder="Search by book title/name. (Type and Enter)"
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
                  to="/damage-items/create"
                  class="z-index-1 btn my-btn-primary float-right"
                >
                  <i class="fa fa-solid fa-plus"></i>
                  Add Damage Item
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
                      <a href="#" @click.prevent="changeShort('damage_date')">
                        Damage Date</a
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
                    <th scope="col">
                      <a href="#" @click.prevent="changeShort('title')"> Title</a>
                      <!-- <a href="#">Name</a> -->
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
                    <th class="text-center text-nowrap" scope="col">
                      <a href="#" @click.prevent="changeShort('quantity')"
                        >Damage Qty.</a
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

                    <th class="text-center text-nowrap" scope="col">Created At</th>
                    <th class="text-center text-nowrap" scope="col">Action</th>
                  </tr>
                  <tr>
                    <!-- <th></th> -->
                    <th class="px-1">
                      <input
                        style="width: 70px"
                        type="text"
                        placeholder="By ID"
                        class=""
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
                        v-model="params.damage_date"
                      />
                    </th>
                    <th colspan="1" class="text-nowarp px-1">
                      <input
                        type="text"
                        placeholder="Search By Book Title"
                        class=""
                        style="width: 100%"
                        v-model="params.title"
                      />
                    </th>
                    <th class="text-nowarp px-1">
                      <input
                        type="text"
                        placeholder="Search By Qty"
                        class=""
                        style="width: 100%"
                        v-model="params.quantity"
                      />
                    </th>
                    <th></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody v-if="damageItems && paginator.totalRecords > 0">
                  <tr v-for="(damage_item,index) in damageItems.data" :key="damage_item.id">
                    <!-- <td class="text-center">
                      <input
                        type="checkbox"
                        :value="damage_item.id"
                        v-model="checked"
                        class="form-check-input"
                      />
                    </td> -->
                    <td style="width: 60px !important" class="text-nowrap">
                      {{ damage_item.id }}
                    </td>
                    <td style="width: 60px !important" class="text-nowrap">
                      {{ damage_item.damage_date }}
                    </td>
              
                    <td class="text-nowrap">
                      <a
                        @click="openModal(damage_item.id)"
                        href="#"
                        data-toggle="modal"
                        data-target="#recordModal"
                        >{{ damage_item.book.title }}</a
                      >
                    </td>
                    <td class="text-nowrap text-center">{{ damage_item.quantity }}</td>
                    <td class="text-nowrap text-center">{{ damage_item.created_at }}</td>

                    <td class="text-right text-nowrap">
                      <div class="btn-group" option="group">
                        <button
                          @click="openModal(damage_item.id)"
                          class="btn btn-sm my-btn-primary"
                          data-toggle="modal"
                          data-target="#recordModal"
                        >
                          <i class="fa fa-eye"></i> View
                        </button>
                        <!-- <router-link
                          :to="`/damage-items/${damage_item.id}`"
                          class="btn btn-sm my-btn-primary"
                          ><i class="fa fa-eye"></i> View</router-link
                        > -->
                        <router-link
                          :to="`/damage-items/${damage_item.id}/edit`"
                          class="btn btn-sm btn-primary px-2 mx-1"
                          ><i class="fa fa-edit"></i> Edit</router-link
                        >
                        <a
                          @click="deleteDamageItem(damage_item.id)"
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
                    <td colspan="13" class="text-center loading-section">
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
                  :data="damageItems"
                  :limit="5"
                  @pagination-change-page="getDamageItems"
                ></pagination>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--Row-->
    <!-- Bootstrap Modal -->
    <ViewDamageItem :record="record" />
    <!-- Modal End -->
  </div>
</template>
<script type="text/javascript">
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";

import { mapActions } from "vuex";
import ViewDamageItem from "./ViewDamageItem.vue";
export default {
  name: "book",
  components: { ViewDamageItem },
  data() {
    return {
      record: {},
      sub_categories: [],
      bookPhotoUrl: null,
      publicPath: window.publicPath,
      checked: [],
      paginator: {
        totalRecords: 0,
        from: 0,
        to: 0,
        current_page: "",
        per_page: "",
      },
      damageItems: {
        type: Object,
        default: null,
      },
      params: {
        paginate: 6,
        id: "",
        title: "",
        damage_date: "",
        quantity: "",
        sort_field: "created_at",
        sort_direction: "desc"
      },
      search: "",
      isLoading: false,
      isRefreshing: false,
      filterFields: {},
    };
  },
  async created() {
  },
  mounted() {
    this.filterFields = { ...this.params };
    this.getDamageItems();
    flatpickr("#datecalander", {
      mode: "range",
      dateFormat: "Y-m-d", // Customize the date format as needed
      // Add more Flatpickr options as needed
    });
  },
  watch: {
    params: {
      handler() {
        this.getDamageItems();
      },
      deep: true,
    },
    search(val, old) {
      if (val.length >= 3 || old.length >= 3) {       
        this.getDamageItems();
      }
    },
  },
  computed: {
  },
  methods: {
    ...mapActions(["fetchCategories"]),
    async getDamageItems(page = 1) {
      this.isLoading = true;
      await axios
        // .get(`/api/products?page=${page}`)
        // .get(`/api/products?page=${page}&book_id=${this.params.book_id}&sort_field=${this.params.sort_field}&sort_direction=${this.params.sort_direction}`)
        .get("/api/damage-items", {
          params: {
            page,
            search: this.search.length >= 3 ? this.search : "",
            ...this.params,
          },
        })
        .then((response) => {
          // console.log(response);
          this.isLoading = false;
          this.damageItems = response.data;
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
      // this.params = { ...this.filterFields };
      this.getDamageItems();
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
    deleteDamageItem(id) {
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
            .delete("/api/damage-items/" + id)
            .then(() => {
              this.getDamageItems();
              Notification.success("Data has been deleted successfully.");
            })
            .catch((error) => {
              // console.log(error);
              if (error.response.status === 422) {
                this.errors = error.response.data.errors;
                Notification.error(error.response.statusText);
              } else if (error.response.status === 401) {
                // statusText = "Unbookized";
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
        '<span class="spinner-border spinner-border-sm" book="status" aria-hidden="true" ></span> Exporting...';
      document.querySelector(".export-btn").innerHTML = loader;
      try {
        axios
          // .get("/api/products-export")
          .get("/api/book-export", { responseType: "arraybuffer" })
          .then((response) => {
            if (response.status == 200) {
              document.querySelector(".export-btn").innerText = "Export to Excel";
              Notification.success("Exported Successfully");
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement("a");
              fileLink.href = fileURL;
              fileLink.setAttribute("download", "book_list.xlsx");
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
        '<span class="spinner-border spinner-border-sm" book="status" aria-hidden="true" ></span>  Exporting...PDF';
      document.querySelector(".export-btn-pdf").innerHTML = loader;
      axios.get("/api/book-export-pdf", { responseType: "blob" }).then((response) => {
        document.querySelector(".export-btn-pdf").innerText = "Export PDF";
        Notification.success("Exported Successfully");
        var fileURL = window.URL.createObjectURL(
          new Blob([response.data], { type: "application/pdf" })
        );
        var fileLink = document.createElement("a");
        fileLink.href = fileURL;
        fileLink.setAttribute("download", "book_list.pdf");
        document.body.appendChild(fileLink);
        fileLink.click();
      });
    },
    openModal(id) {
      // Fetch the record details from the server using Axios or a similar library
      axios
        .get(`api/damage-items/${id}`)
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
