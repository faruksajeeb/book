<template>
  <div class="row">
    <div class="col-md-12 offset-md-0">
      <div class="card shadow-sm my-2">
        <div class="card-header py-0 my-bg-success">
          <!-- <h3 class="text-white-900" v-if="isNew"><i class="fa fa-plus"></i> Add Sale Return</h3>
          <h3 class="text-white-900" v-else><i class="fa fa-pencil"></i> Edit Sale Return</h3> -->
          <!-- <p class="text-white m-0">
            ফরমের লাল তারকা (<span class="text-danger">*</span>) চিহ্নিত ঘরগুলো অবশ্যই
            পূরণ করুন। অন্যান্য ঘরগুলো পূরণ ঐচ্ছিক।
          </p> -->
        </div>
        <div class="card-body p-3">
          <div class="form">
            <div v-if="!isNew && !sale">
              <!-- <LoadingSpinner /> -->
            </div>
            <div class="row">
              <div class="col-md-6">
                <input
                  type="text"
                  class="form-control"
                  placeholder="Search by publication, author, book name, isbn or genre..."
                  v-model="search"
                />
                <div class="categories my-2">
                  <a
                    href="#"
                    class="p-2 m-1 text-danger text-nowrap"
                    @click="getCatWiseBook('')"
                    >All Category</a
                  >
                  <a
                    href="#"
                    class="p-2 m-1 text-danger text-nowrap"
                    @click="getCatWiseBook(category.id)"
                    v-for="category in categories"
                    >{{ category.category_name }}</a
                  >
                </div>
                <div class="row product-view" v-if="books && paginator.totalRecords > 0">
                  <div class="col-xl-4 col-md-6 mb-2 px-1" v-for="book in books.data">
                    <div class="card h-100">
                      <div class="card-body">
                        <div class="row align-items-center">
                          <div class="col mr-2 pb-0 mb-0">
                            <div
                              class="text-xs font-weight-bold text-center text-uppercase mb-1"
                            >
                              {{ book.title }}
                            </div>
                            <div class="text-center">
                              <img
                                class="text-center"
                                :src="
                                  `${publicPath}assets/img/book/thumbnail/` + book.photo
                                "
                                alt=""
                                width="100"
                              />
                            </div>
                            <div class="text-center font-weight-bold text-gray-800">
                              ৳ {{ book.price }}
                            </div>
                            <div class="py-0 text-muted text-xs text-center">
                              <span class="text-success mr-2"
                                ><i class="fa fa-pen"></i>
                                {{ book.author.author_name }}</span
                              >
                            </div>
                            <div class="text-center">
                              <small>In Stock: {{ book.stock_quantity }}</small>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="card-footer text-center py-1">
                        <button
                          @click="addToCart(book)"
                          :class="`btn btn-sm m-1  my-btn-primary addToCart${book.id}`"
                        >
                          <i class="fa fa-plus"></i> Add To Cart
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center loading-section">
                  <loader v-if="isLoading"></loader>
                  <NoRecordFound v-else />
                </div>
                <div class="row">
                  <div class="col-xl-12 col-md-12">
                    <pagination
                      align="center"
                      :data="books"
                      :limit="3"
                      @pagination-change-page="getBooks"
                    ></pagination>
                  </div>
                </div>
              </div>
              <div
                class="col-md-6 py-2"
                style="border-radius: 5px; border: 2px solid #c9f4aa"
              >
                <AlertError :form="form" />
                <form
                  id="form"
                  class="sale"
                  enctype="multipart/form-data"
                  @submit.prevent="submitForm"
                  @keydown="form.onKeydown($event)"
                >
                  <div class="input-group mb-1 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-4 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Customer
                        <div class="text-danger mx-1"> *</div></label
                      >
                    </div>
                    <select
                      class="custom-select mx-0 pe-0"
                      v-model="form.customer_id"
                      @change="getDiscountPercentage()"
                      :class="{ 'is-invalid': form.errors.has('customer_id') }"
                    >
                      <option value="" disabled selected>Choose...</option>
                      <option
                        :value="customer.id"
                        v-for="customer in customers"
                        :key="customer.id"
                      >
                        {{ customer.customer_name }}
                      </option>
                    </select>
                    <HasError :form="form" field="customer_id" />
                  </div>
                  <div class="input-group mb-1 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-4 mx-0">
                      <label
                        class="input-group-text col-md-12"
                        for="inputGroupSelect01"
                        title=""
                        >Return Date
                        <div class="text-danger mx-1"> *</div></label
                      >
                    </div>
                    <input
                      type="text"
                      class="form-control datecalender"
                      id="datecalander"
                      autocomplete="off"
                      placeholder="Choose sale return date"
                      name="sale_return_date"
                      v-model="form.sale_return_date"
                      @input="clearError('sale_return_date')"
                      :class="{ 'is-invalid': form.errors.has('sale_return_date') }"
                    />
                    <HasError :form="form" field="sale_return_date" />
                  </div>
                  <fieldset class="reset my-1 p-1" style="background-color: #c9f4aa">
                    <legend class="text-white my-btn-primary p-1 reset">ফেরত কপি:</legend>
                    <table class="table table-sm">
                      <thead>
                        <th class="text-left px-1">Book Name</th>
                        <th class="text-right px-1">Unit Price</th>
                        <th class="text-center px-1">Qty</th>
                        <th class="text-right px-1">Sub Total</th>
                        <th style="width: 50px"></th>
                      </thead>
                      <tbody v-if="cartItems.length > 0">
                        <tr v-for="(item, index) in cartItems" :key="index">
                          <td class="px-1">{{ item.title }}</td>
                          <td class="px-1 text-right">
                            <input
                              type="number"
                              v-model="item.price"
                              @input="updatePrice(index)"
                              style="width: 80px"
                            />
                          </td>
                          <td class="px-1">
                            <!-- {{ item.quantity }} -->
                            <input
                              type="number"
                              v-model="item.quantity"
                              @input="updateQuantity(index)"
                              style="width: 50px"
                            />
                          </td>
                          <td class="text-right px-1">
                            {{ item.price * item.quantity }}
                          </td>
                          <td class="text-center px-1">
                            <a href="#" @click="removeFromCart(index)"
                              ><i class="fa fa-trash text-danger"></i
                            ></a>
                          </td>
                        </tr>
                        <tr>
                          <td class="fw-bold px-1" colspan="2">TOTAL</td>
                          <td class="fw-bold px-1"></td>
                          <td class="fw-bold px-1 text-right">{{ calculateTotal() }}</td>
                          <td class="fw-bold px-1"></td>
                        </tr>
                        <tr>
                          <td class="text-bold px-1" colspan="2">
                            Discount Percentage(%)
                          </td>
                          <td class="text-bold px-1">
                            <input
                              type="number"
                              style="width: 50px"
                              v-model="discountPercentage"
                              @input="updateDiscount"
                            />
                          </td>
                          <td class="text-bold px-1 text-right">
                            {{ calculateDiscountAmount() }}
                          </td>
                          <td class="text-bold px-1"></td>
                        </tr>
                        <tr>
                          <td class="text-bold px-1" colspan="2">Vat Rate(%)</td>
                          <td class="text-bold px-1">
                            <input
                              type="number"
                              style="width: 50px"
                              v-model="vatPercentage"
                              @input="updateVat"
                            />
                          </td>
                          <td class="text-bold px-1 text-right">
                            {{ calculateVatAmount() }}
                          </td>
                          <td class="text-bold px-1"></td>
                        </tr>
                        <tr>
                          <td class="text-bold px-1" colspan="2">Shipping Cost</td>
                          <td class="text-bold px-1"></td>
                          <td class="text-bold px-1 text-right">
                            <input
                              type="number"
                              style="width: 100px"
                              v-model="form.shipping_amount"
                              @input="updateShippingCost"
                            />
                          </td>
                          <td class="text-bold px-1"></td>
                        </tr>

                        <tr>
                          <td class="fw-bold px-1" colspan="2">NET TOTAL</td>
                          <td class="fw-bold px-1"></td>
                          <td class="fw-bold px-1 text-right">
                            {{ calculateNetTotal() }}
                          </td>
                          <td class="fw-bold px-1"></td>
                        </tr>
                      </tbody>
                      <tbody v-else>
                        <tr>
                          <td colspan="5" class="py-3 text-center">
                            <div v-if="!isNew && !sale">
                              <LoadingSpinner />
                            </div>
                            Cart Empty <HasError :form="form" field="cart_items" />
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </fieldset>

                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-5 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Attach File (If any)
                        <div class="text-danger"></div
                      ></label>
                    </div>
                    <input
                      type="file"
                      class="form-control col-md-8"
                      placeholder="Choose..."
                      name="attach_file"
                      @change="onFileChange"
                      @input="clearError('attach_file')"
                      accept="image/*,application/pdf"
                      :class="{ 'is-invalid': form.errors.has('attach_file') }"
                    />
                    <HasError :form="form" field="attach_file" />
                    [Allow File type:jpeg,png,jpg,gif,svg,pdf & Max Size:2MB]
                  </div>
                  <div class="image-item">
                    <img
                      v-if="imageUrl && fileExtension != 'pdf'"
                      :src="imageUrl"
                      alt="Image Preview"
                      width="120"
                    />
                    <button
                      class="remove-button"
                      type="button"
                      v-if="imageUrl"
                      @click="removeSingleImage(image, key)"
                    >
                      x
                    </button>
                  </div>
                  <iframe
                    v-if="imageUrl && fileExtension == 'pdf'"
                    :src="imageUrl"
                    width="200"
                    height="200"
                  ></iframe>

                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-4 mx-0">
                      <label
                        class="input-group-text col-md-12"
                        for="inputGroupSelect01"
                        title=""
                        >Sale Return Note:
                        <div class="text-danger"></div
                      ></label>
                    </div>
                    <textarea
                      name=""
                      id=""
                      cols="30"
                      rows="2"
                      class="form-control"
                      v-model="form.sale_return_note"
                      >{{ form.sale_return_note }}</textarea
                    >
                  </div>
                  <div class="form-group mt-2">
                    <!-- <div v-if="form.progress">Progress: {{ form.progress.percentage }}%</div> -->
                    <router-link
                      to="/sale-returns"
                      class="btn btn-lg btn-primary px-2 mx-1"
                      ><i class="fa fa-list"></i> Manage Sale Return</router-link
                    >
                    <save-button v-if="isNew" :is-submitting="isSubmitting"></save-button>
                    <save-changes-button
                      v-else
                      :is-submitting="isSubmitting"
                    ></save-changes-button>
                    <!-- <reset-button @reset-data="resetData" /> -->
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script type="text/javascript">
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";

import { mapActions } from "vuex";
export default {
  data() {
    return {
      isSubmitting: false,
      isLoading: false,
      imageUrl: null,
      sale: false,
      cartItems: [],
      publicPath: window.publicPath,
      discountPercentage: 0, // Initialize with no discount
      vatPercentage: 0, // Initialize with no discount
      fileExtension: null,
      paginator: {
        totalRecords: 0,
        from: 0,
        to: 0,
        current_page: "",
        per_page: "",
      },
      customers: [],
      payment_methods: [],
      books: {
        type: Object,
        default: null,
      },
      form: new Form({
        customer_id: "",
        sale_return_date: "",
        // cartItems: [],
        total_amount: 0,
        courtesy_total_amount: 0,
        discount_percentage: 0,
        discount_amount: 0,
        vat_percentage: 0,
        vat_amount: 0,
        net_amount: 0,
       
        sale_return_note: "",
        attach_file: null,
        shipping_amount: 0,
        shipping_address: "",
      }),
      params: {
        paginate: 6,
        id: "",
        title: "",
        category_id: "",
        sort_field: "created_at",
        sort_direction: "desc",
      },
      search: "",
    };
  },
  watch: {
    params: {
      handler() {
        this.getBooks();
      },
      deep: true,
    },
    search(val, old) {
      if (val.length >= 3 || old.length >= 3) {
        this.getBooks();
      }
    },
  },
  computed: {
    isNew() {
      return !this.$route.path.includes("edit");
    },
    categories() {
      return this.$store.state.categories;
    },
  },
  mounted() {
    this.filterFields = { ...this.params };
    // this.form.cartItems = this.cartItems;
    this.getBooks();
    flatpickr("#datecalander", {
      dateFormat: "Y-m-d", // Customize the date format as needed
      // Add more Flatpickr options as needed
    });
  },
  async created() {
    this.fetchCategories();
    this.customers = this.$store.getters.getCustomers;
    this.payment_methods = this.$store.getters.getPaymentMethods;
    if (this.payment_methods.length == 0) {
      const response = await axios.get("/api/get-payment-methods");
      this.payment_methods = response.data;
    }
    if (this.customers.length == 0) {
      const response = await axios.get("/api/get-customers");
      this.customers = response.data;
    }
    if (!this.isNew) {
      const response = await axios.get(`/api/sale-returns/${this.$route.params.id}`);

      this.sale = true;
      this.form.customer_id = response.data.sale_return.customer_id;
      this.form.sale_return_date = response.data.sale_return.sale_return_date;
      this.discountPercentage = response.data.sale_return.discount_percentage;
      this.form.discount_percentage = response.data.sale_return.discount_percentage;

      this.form.attach_file = response.data.sale_return.attach_file;
      this.cartItems = response.data.sale_return_details;
      this.form.sale_return_note = response.data.sale_return.sale_return_note;

      this.imageUrl =
        `${window.publicPath}assets/img/sale_return/` + response.data.sale_return.attach_file;

      const fileName = response.data.sale_return.attach_file;
      if (fileName) {
        const parts = fileName.split(".");
        if (parts.length > 1) {
          // Get the last part as the file extension
          this.fileExtension = parts[parts.length - 1].toLowerCase();
        }
      }
    } else {
      this.resetData();
    }
  },
  methods: {
    ...mapActions(["fetchCategories"]),

    onFileChange(e) {
      let selectedFile = e.target.files[0];
      if (selectedFile) {
        const allowedFileTypes = [
          "image/jpeg",
          "image/jpg",
          "image/png",
          "image/gif",
          "image/svg",
          "application/pdf",
        ];
        if (selectedFile.size > 2048 * 1024) {
          // Change this to your desired maximum file size in bytes
          this.form.errors.set("file", "File size exceeds the maximum allowed size.");
          Notification.error("File size exceeds the maximum allowed size.");
        }
        if (!allowedFileTypes.includes(selectedFile.type)) {
          this.form.errors.set(
            "file",
            " File type is not supported. Please choose a valid file type."
          );
          Notification.error(
            " File type is not supported. Please choose a valid file type."
          );
        }
        this.form.attach_file = selectedFile;
        if (selectedFile.type == "application/pdf") {
          this.fileExtension = "pdf";
        }

        const reader = new FileReader();

        reader.onload = (e) => {
          this.imageUrl = e.target.result;
        };
        reader.readAsDataURL(selectedFile);
      }
    },
    async getDiscountPercentage() {
      this.form.errors.clear("customer_id");
      // this.isLoading = true;
      const customerId = this.form.customer_id;
      const response = await axios.get(
        `/api/get-customer-discount-percentage/${customerId}`
      );
      // this.isLoading = false;
      this.form.discount_percentage = response.data.discount_percentage;
      this.discountPercentage = response.data.discount_percentage
        ? response.data.discount_percentage
        : 0;
    },
    removeSingleImage(image, index) {
      this.imageUrl = null;
      this.form.attach_file = null;
    },
    async getBooks(page = 1) {
      this.isLoading = true;
      await axios
        .get("/api/books", {
          params: {
            page,
            search: this.search.length >= 3 ? this.search : "",
            ...this.params,
          },
        })
        .then((response) => {
          // console.log(response);
          this.isLoading = false;
          this.books = response.data;
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
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    getCatWiseBook(categoryId) {
      this.params.category_id = categoryId;
    },
    addToCart(book) {
      const cartItem = this.cartItems.find((item) => item.id === book.id);
      if (cartItem) {
        Notification.success(`Item '${cartItem.title}' Qty. has been updated!`);
        cartItem.quantity++; // If the product already exists, increment its quantity
      } else {
        Notification.success(`Item Added!`);

        document.querySelector(`.addToCart${book.id}`).innerHTML =
          '<i class="fa fa-check"></i> Added';
        book.quantity = 1;
        this.cartItems.push(book);
      }
      // this.calculateTotal();
    },
    removeFromCart(index) {
      // Find the index of the item in the cart array
      // const index = this.cartItems.findIndex(item => item.id === itemId);

      if (index !== -1) {
        document.querySelector(`.addToCart${this.cartItems[index].id}`).innerHTML =
          '<i class="fa fa-plus"></i> Add To Cart';
        // Remove the item from the cart array
        this.cartItems.splice(index, 1);
        Notification.success(`Item Removed!`);
        // this.calculateTotal();
      }
    },
    updatePrice(index) {
      // Ensure price is a positive number
      if (this.cartItems[index].price < 0) {
        this.cartItems[index].price = 0;
      }
    },
    async updateQuantity(index) {
      // Ensure quantity is a positive number
      if (this.cartItems[index].quantity < 1) {
        this.cartItems[index].quantity = 1;
      }
    },
    updateDiscount() {
      // Ensure discountPercentage is within a valid range (e.g., between 0 and 100)
      if (this.discountPercentage < 0) {
        this.discountPercentage = 0;
      } else if (this.discountPercentage > 100) {
        this.discountPercentage = 100;
      }
    },
    updateVat() {
      // Ensure vatPercentage is within a valid range (e.g., between 0 and 100)
      if (this.vatPercentage < 0) {
        this.vatPercentage = 0;
      } else if (this.vatPercentage > 100) {
        this.vatPercentage = 100;
      }
    },
    updateShippingCost() {
      this.calculateNetTotal();
      if (this.form.shipping_amount < 0) {
        this.form.shipping_amount = 0;
      }
    },
    calculateDiscountAmount() {
      const totalBeforeDiscount = this.calculateTotal();
      const discountAmount = (totalBeforeDiscount * this.discountPercentage) / 100;
      this.form.discount_percentage = this.discountPercentage;
      this.form.discount_amount = discountAmount;
      // const totalAfterDiscount = totalBeforeDiscount - discountAmount;
      return discountAmount.toFixed(2);
    },
    calculateVatAmount() {
      const totalBeforeDiscount = this.calculateTotal();
      const vatAmount = (totalBeforeDiscount * this.vatPercentage) / 100;
      this.form.vat_percentage = this.vatPercentage;
      this.form.vat_amount = vatAmount;
      return vatAmount.toFixed(2);
    },
    calculateTotal() {
      return this.cartItems.reduce((total, item) => {
        const res = total + item.price * item.quantity;
        this.form.total_amount = res;
        return res;
      }, 0);
    },
    calculateNetTotal() {
      const totalBeforeDiscount = this.calculateTotal();
      const discountAmount = (totalBeforeDiscount * this.discountPercentage) / 100;
      const totalAfterDiscount = totalBeforeDiscount - discountAmount;
      const vatAmount = (totalAfterDiscount * this.vatPercentage) / 100;
      const totalWithVATShippingCost =
        totalAfterDiscount + vatAmount + this.form.shipping_amount;
      this.form.net_amount = totalWithVATShippingCost;
      return totalWithVATShippingCost.toFixed(2);
    },
    async submitForm() {
      this.isSubmitting = true;
      // console.log(this.form.cartItems);
      if (this.isNew) {
        await this.form
          .post("/api/sale-returns", {
            params: {
              cart_items: this.cartItems,
              courtesy_cart_items: this.courtesyCartItems,
              ...this.form,
            },
          })
          .then(() => {
            this.$router.push({ name: "sale-returns" });
            Notification.success(`Create sale return successfully!`);
          })
          .catch((error) => {
            // console.log(error);
            if (error.response.status === 422) {
              this.errors = error.response.data.errors;
              Notification.error("Validation Errors!");
            } else if (error.response.status === 401) {
              // statusText = "Unsaleized";
              this.errors = {};
              Notification.error(error.response.data.error);
            } else {
              Notification.error(error.response.statusText);
            }
          })
          .finally(() => {
            // always executed;
            this.isSubmitting = false;
          });
      } else {
        try {
          await this.form
            .post(`/api/sale-returns/${this.$route.params.id}`, {
              params: {
                cart_items: this.cartItems,
                courtesy_cart_items: this.courtesyCartItems,
                ...this.form,
              },
            })
            .then((response) => {
              Notification.success("sale info updated");
              this.$router.push("/sale-returns");
            })
            .catch((error) => {
              Notification.error(error);
            })
            .finally(() => {
              // always executed;
              this.isSubmitting = false;
            });
        } catch (error) {
          Notification.error(error);
        }
      }
    },
    resetData() {
      this.form.clear();
      this.form.reset();
    },
    clearError(fieldName) {
      this.form.errors.clear(fieldName);
    },
  },
};
</script>

<style type="text/css" scoped>
td {
  padding: 1px;
  font-size: 12px;
}
ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
}
</style>
