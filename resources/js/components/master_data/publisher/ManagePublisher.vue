<template>
  <div>
    <div class="row">
      <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
          <div
            class="card-header py-3 "
          >
            <h3 class="m-0 font-weight-bold" title="">Publisher List</h3>
            <p class="text-secondary m-0">Stores information about book publishers, including their names, addresses, and contact details.</p>
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
                  placeholder="Search by publisher. (Type and Enter)"
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
                  to="/publishers/create"
                  class="z-index-1 btn my-btn-primary float-right"
                >
                  <i class="fa fa-solid fa-plus"></i>
                  Add publisher
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
                    <!-- <th scope="col">
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
                    </th> -->
                    <th class="text-center">Photo</th>
                    <th scope="col">
                      <a href="#" @click.prevent="changeShort('publisher_name')"
                        > Name</a
                      >
                      <!-- <a href="#">Name</a> -->
                      <span
                        v-if="
                          this.params.sort_field == 'publisher_name' &&
                          this.params.sort_direction == 'asc'
                        "
                        >↑</span
                      >
                      <span
                        v-if="
                          this.params.sort_field == 'publisher_name' &&
                          this.params.sort_direction == 'desc'
                        "
                        >↓</span
                      >
                    </th>
                    <th class="text-center"> Phone</th>
                    <th class="text-center"> Email</th>
                    <th class="text-center"> Address</th>
                    <th class="text-center">Action</th>
                  </tr>
                  <tr>
                    <th></th>
                    <!-- <th class="p-0" colspan="2">
                      <input
                        type="text"
                        placeholder="Search By ID"
                        class="form-control-sm w-100"
                        v-model="params.id"
                      />
                    </th> -->
                    <th class="p-0" colspan="2">
                      <input
                        type="text"
                        placeholder="Search By publisher Name"
                        class="form-control-sm w-100"
                        v-model="params.publisher_name"
                      />
                    </th>
                    <th class="p-0">
                      <input
                        type="text"
                        placeholder="Search By publisher Phone"
                        class="form-control-sm w-100"
                        v-model="params.publisher_phone"
                      />
                    </th>
                    <th class="p-0">
                      <input
                        type="text"
                        placeholder="Search By publisher Email"
                        class="form-control-sm w-100"
                        v-model="params.publisher_email"
                      />
                    </th>
                    <th class="p-0" colspan="2">
                      <input
                        type="text"
                        placeholder="Search By publisher Address"
                        class="form-control-sm w-100"
                        v-model="params.publisher_address"
                      />
                    </th>
                  </tr>
                </thead>
                <tbody v-if="publishers && paginator.totalRecords > 0">
                  <tr v-for="publisher in publishers.data" :key="publisher.id">
                    <td class="text-center">
                      <input
                        type="checkbox"
                        :value="publisher.id"
                        v-model="checked"
                        class="form-check-input"
                      />
                    </td>
                    <!-- <td class="text-nowrap">{{ publisher.id }}</td> -->
                    <td>
                      <img
                        :src="
                          `${publicPath}assets/img/publisher/thumbnail/` +
                          publisher.publisher_photo
                        "
                        alt=""
                        width="30"
                      />
                    </td>
                    <td>{{ publisher.publisher_name }}</td>
                    <td>{{ publisher.publisher_phone }}</td>
                    <td>{{ publisher.publisher_email }}</td>
                    <td>{{ publisher.publisher_address }}</td>

                    <td class="text-right text-nowrap">
                      <div class="btn-group" option="group">
                        <button @click="openModal(publisher.id)" class="btn btn-sm my-btn-primary"  data-toggle="modal" data-target="#recordModal" > <i class="fa fa-eye"></i> View </button>
                        <!-- <router-link
                          :to="`/publishers/${publisher.id}`"
                          class="btn btn-sm my-btn-primary"
                          ><i class="fa fa-eye"></i> View</router-link
                        > -->
                        <router-link
                          :to="`/publishers/${publisher.id}/edit`"
                          class="btn btn-sm btn-primary px-2 mx-1"
                          ><i class="fa fa-edit"></i> Edit</router-link
                        >
                        <a
                          @click="deletepublisher(publisher.id)"
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
                  :data="publishers"
                  :limit="5"
                  @pagination-change-page="getPublishers"
                ></pagination>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--Row-->
    <!-- Bootstrap Modal -->
    <Viewpublisher :record="record" />
    <!-- Modal End -->
  </div>
</template>
<script type="text/javascript">
import ViewPublisher from './ViewPublisher.vue'
export default {
  name: "publisher",
  components:{ViewPublisher},
  data() {
    return {
      record: {},
      publisherPhotoUrl: null,
      publicPath: window.publicPath,
      checked: [],
      paginator: {
        totalRecords: 0,
        from: 0,
        to: 0,
        current_page: "",
        per_page: "",
      },
      publishers: {
        type: Object,
        default: null,
      },
      params: {
        paginate: 5,
        id: "",
        publisher_name: "",
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
    this.getPublishers();
  },
  watch: {
    params: {
      handler() {
        this.getPublishers();
      },
      deep: true,
    },
    search(val, old) {
      if (val.length >= 3 || old.length >= 3) {
        this.getPublishers();
      }
    },
  },
  computed: {
    categories() {
      return this.$store.state.categories;
    },
  },
  methods: {
    async getPublishers(page = 1) {
      this.isLoading = true;
      await axios
        // .get(`/api/products?page=${page}`)
        // .get(`/api/products?page=${page}&publisher_id=${this.params.publisher_id}&sort_field=${this.params.sort_field}&sort_direction=${this.params.sort_direction}`)
        .get("/api/publishers", {
          params: {
            page,
            search: this.search.length >= 3 ? this.search : "",
            ...this.params,
          },
        })
        .then((response) => {
          // console.log(response);
          this.isLoading = false;
          this.publishers = response.data;
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
      this.getPublishers();
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
    deletepublisher(id) {
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
            .delete("/api/publishers/" + id)
            .then(() => {
              this.getPublishers();
              Notification.success("Data has been deleted successfully.");
            })
            .catch((error) => {
              // console.log(error);
              if (error.response.status === 422) {
                this.errors = error.response.data.errors;
                Notification.error(error.response.statusText);
              } else if (error.response.status === 401) {
                // statusText = "Unpublisherized";
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
        '<span class="spinner-border spinner-border-sm" publisher="status" aria-hidden="true" ></span> Exporting...';
      document.querySelector(".export-btn").innerHTML = loader;
      try {
        axios
          // .get("/api/products-export")
          .get("/api/publisher-export", { responseType: "arraybuffer" })
          .then((response) => {
            if (response.status == 200) {
              document.querySelector(".export-btn").innerText = "Export to Excel";
              Notification.success("Exported Successfully");
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement("a");
              fileLink.href = fileURL;
              fileLink.setAttribute("download", "publisher_list.xlsx");
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
        '<span class="spinner-border spinner-border-sm" publisher="status" aria-hidden="true" ></span>  Exporting...PDF';
      document.querySelector(".export-btn-pdf").innerHTML = loader;
      axios.get("/api/publisher-export-pdf", { responseType: "blob" }).then((response) => {
        document.querySelector(".export-btn-pdf").innerText = "Export PDF";
        Notification.success("Exported Successfully");
        var fileURL = window.URL.createObjectURL(
          new Blob([response.data], { type: "application/pdf" })
        );
        var fileLink = document.createElement("a");
        fileLink.href = fileURL;
        fileLink.setAttribute("download", "publisher_list.pdf");
        document.body.appendChild(fileLink);
        fileLink.click();
      });
    },
    openModal(id) {
      // Fetch the record details from the server using Axios or a similar library
      axios
        .get(`api/publishers/${id}`)
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
