<template>
  <div class="row">
    <div class="col-md-12 offset-md-0">
      <div class="card shadow-sm my-2">
        <div class="card-header py-2 my-bg-success">
          <h3 class="text-white-900" v-if="isNew">
            <i class="fa fa-plus"></i> Add Purchase
          </h3>
          <h3 class="text-white-900" v-else>
            <i class="fa fa-pencil"></i> Edit Purchase
          </h3>
          <!-- <p class="text-white m-0">
            ফরমের লাল তারকা (<span class="text-danger">*</span>) চিহ্নিত ঘরগুলো অবশ্যই
            পূরণ করুন। অন্যান্য ঘরগুলো পূরণ ঐচ্ছিক।
          </p> -->
        </div>
        <div class="card-body p-3">
          <div class="form">
            <div v-if="!isNew && !purchase">
              <!-- <LoadingSpinner /> -->
            </div>
            <div class="row">
              <div class="col-md-6" >
                <input
                  type="text"
                  class="form-control form-control-lg full-width"
                  placeholder="Search by publication, author, book name, isbn or genre..."
                  v-model="search"
                />
                <div class="categories my-2">
                  <a
                    href="#"
                    class="p-2 m-1 my-text-primary text-nowrap"
                    @click="getCatWiseBook('')"
                    >All Category</a
                  >
                  <a
                    href="#"
                    class="p-2 m-1 my-text-primary text-nowrap"
                    @click="getCatWiseBook(category.id)"
                    v-for="category in categories"
                    >{{ category.category_name }}</a
                  >
                </div>
                <SearchResult
                  v-if="searchResults.length"
                  :searchResults="searchResults"
                  @add-to-cart="handleAddToCart"
                  :cartStatus="cartStatus"
                  />
                
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
                  class="purchase"
                  enctype="multipart/form-data"
                  @submit.prevent="submitForm"
                  @keydown="form.onKeydown($event)"
                >
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-4 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        ><i class="fa fa-user"> Supplier</i> 
                        <div class="text-danger"> *</div></label
                      >
                    </div>
                    <select
                      class="custom-select mx-0 pe-0"
                      v-model="form.supplier_id"
                      :class="{ 'is-invalid': form.errors.has('supplier_id') }"
                    >
                      <option value="" disabled selected>Choose...</option>
                      <option
                        :value="supplier.id"
                        v-for="supplier in suppliers"
                        :key="supplier.id"
                      >
                        {{ supplier.supplier_name }}
                      </option>
                    </select>
                    <HasError :form="form" field="supplier_id" />
                  </div>
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-4 mx-0">
                      <label
                        class="input-group-text col-md-12"
                        for="inputGroupSelect01"
                        title=""
                        ><i class="fa fa-calendar" aria-hidden="true"> Purchase Date</i>  
                        <div class="text-danger"> *</div></label
                      >
                    </div>
                    <input
                      type="text"
                      class="form-control datecalender"
                      id="datecalander"
                      autocomplete="off"
                      placeholder="Choose purchase date"
                      name="purchase_date"
                      v-model="form.purchase_date"
                      :class="{ 'is-invalid': form.errors.has('purchase_date') }"
                    />
                    <HasError :form="form" field="purchase_date" />
                  </div>

                  <!-- <PurchaseCart :items="purchaseCartItems" /> -->
                  <fieldset class="reset my-1 p-1" style="background-color: #c9f4aa">
                    <legend class="text-white my-btn-primary p-1 reset">
                      ক্রয়কৃত কপি:
                    </legend>
                    <table class="table table-sm">
                      <thead>
                        <th class="text-left px-1">Book Name</th>
                        <th class="text-left px-1">Variant</th>
                        <th class="text-right px-1">Cost Price</th>
                        <th class="text-center px-1">Qty</th>
                        <th>Discount</th>
                        <th class="text-right px-1" style="width: 50px">Total</th>
                        <th style="width: 50px"></th>
                      </thead>
                      <tbody v-if="cartItems.length > 0">
                        <tr v-for="(item, index) in cartItems" :key="`${item.id}-${item.variantId}`">
                          <td class="px-1">{{ isNew ? item.title : item.book.title }}</td>
                          <td class="px-1">{{ item.variant ? formatVariantLabel(item.variant) : "No Variant" }}</td>
                          <td class="px-1 text-right">
                            <input
                              type="number" 
                              v-model="item.price"
                              @input="updatePrice(index)"
                              style="width: 100px"
                            />
                          </td>
                          <td class="px-1">
                            <!-- {{ item.quantity }} -->
                            <input
                              style="width: 80px"
                              type="number"
                              v-model="item.quantity"
                              @input="updateQuantity(index)"
                            />
                          </td>
                          <td>
                          <input
                            style="width: 80px"
                            type="number"
                            v-model="item.discount_amount"
                            min="0"
                            value="0"
                            placeholder="Enter discount"
                            @input="updateTotal(item)"
                          />
                        </td>
                          <td class="text-right px-1">
                            <!-- {{ item.price * item.quantity }} -->
                            {{ calculateSubTotal(item) }}
                          </td>
                          <td class="text-center px-1">
                            <a href="#" @click="removeFromCart(index, item.variant)"
                              ><i class="fa fa-trash text-danger"></i
                            ></a>
                          </td>
                        </tr>
                        <tr>
                          <td class="fw-bold px-1" colspan="4">TOTAL</td>
                          <td class="fw-bold px-1"></td>
                          <td class="fw-bold px-1 text-right">{{ calculateTotal() }}</td>
                          <td class="fw-bold px-1"></td>
                        </tr>
                        <tr>
                          <td class="text-bold px-1" colspan="4">
                            Discount Percentage(%)
                          </td>
                          <td class="text-bold px-1">
                            <input
                              type="number"
                              style="width: 80px"
                              v-model="form.discount_percentage"
                              @input="updateDiscount"
                            />
                          </td>
                          <td class="text-bold px-1 text-right">
                            {{ calculateDiscountAmount() }}
                          </td>
                          <td class="text-bold px-1"></td>
                        </tr>
                        <tr>
                          <td class="text-bold px-1" colspan="4">Vat Rate(%)</td>
                          <td class="text-bold px-1">
                            <input
                              type="number"
                              style="width: 80px"
                              v-model="form.vat_percentage"
                              @input="updateVat"
                              
                            />
                          </td>
                          <td class="text-bold px-1 text-right">
                            {{ calculateVatAmount() }}
                          </td>
                          <td class="text-bold px-1"></td>
                        </tr>
                        <tr>
                          <td class="fw-bold px-1" colspan="4">NET TOTAL</td>
                          <td class="fw-bold px-1"></td>
                          <td class="fw-bold px-1 text-right">
                            {{ calculateNetTotal() }}
                          </td>
                          <td class="fw-bold px-1"></td>
                        </tr>
                        <tr>
                          <td class="fw-bold px-1" colspan="3">Pay Amount</td>
                          <td class="fw-bold px-1 text-right" colspan="3">
                            <input
                              type="number"
                              style="width: 180px"
                              class="text-right "
                              v-model="form.pay_amount"
                              :class="{ 'is-invalid': form.errors.has('pay_amount') }"
                              @input="updatePayAmount"
                            />
                            <HasError :form="form" field="pay_amount" />
                          </td>
                          <td class="fw-bold px-1"></td>
                        </tr>
                        <tr>
                          <td class="fw-bold px-1" colspan="4">Due Amount</td>
                          <td class="fw-bold px-1"></td>
                          <td class="fw-bold px-1 text-right">{{ dueAmount() }}</td>
                          <td class="fw-bold px-1"></td>
                        </tr>
                        <tr v-show="form.pay_amount > 0">
                          <td class="fw-bold px-1" colspan="2">
                            Payment Method
                            <div class="text-danger">*</div>
                          </td>
                          <td class="fw-bold px-1" colspan="5">
                            <select
                              name="payment_method"
                              v-model="form.payment_method"
                              class="form-select"
                              :class="{ 'is-invalid': form.errors.has('payment_method') }"
                            >
                              <option value="" selected>--select payment method--</option>
                              <option :value="payment_method.id" v-for="payment_method in payment_methods">{{ payment_method.name }}</option>
                            </select>
                            <HasError :form="form" field="payment_method" />
                          </td>
                        </tr>
                        <tr v-show="form.pay_amount > 0">
                          <td class="fw-bold px-1" colspan="1">Payment Descriptin</td>
                          <td class="fw-bold px-1" colspan="6">
                            <textarea
                              type="text"
                              class="form-control"
                              v-model="form.payment_description"
                              :class="{
                                'is-invalid': form.errors.has('payment_description'),
                              }"
                              placeholder="Enter payment description here"
                            />
                            <HasError :form="form" field="payment_description" />
                          </td>
                        </tr>
                        <tr v-show="form.pay_amount > 0">
                          <td class="fw-bold px-1" colspan="1">Paid By</td>
                          <td class="fw-bold px-1" colspan="6">
                            <input
                              type="text"
                              class="form-control"
                              v-model="form.paid_by"
                              :class="{ 'is-invalid': form.errors.has('paid_by') }"
                              placeholder="Enter paid by"
                            />
                            <HasError :form="form" field="paid_by" />
                          </td>
                        </tr>
                      </tbody>
                      <tbody v-else>
                        <tr>
                          <td colspan="6" class="py-3 text-center">
                            <div v-if="!isNew && !purchase">
                              <LoadingSpinner />
                            </div>
                            Cart Empty <HasError :form="form" field="cart_items" />
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </fieldset>

                  <br />
                  <fieldset class="reset my-1 p-1" style="background-color: #ffeebb">
                    <legend class="text-white my-btn-danger p-1 reset">
                      সৌজন্য কপি:
                    </legend>
                    <table class="table table-sm">
                      <thead>
                        <th class="text-left px-1">Book Name</th>
                        <th class="text-left px-1">Variant</th>
                        <th class="text-right px-1">Unit Price</th>
                        <th class="text-center px-1">Qty</th>
                        <th class="text-right px-1">Sub Total</th>
                        <th style="width: 50px"></th>
                      </thead>
                      <tbody v-if="courtesyCartItems.length > 0">
                        <tr v-for="(citem, index) in courtesyCartItems" :key="`${citem.id}-${citem.variantId}`">
                          <td class="px-1">{{ citem.title }}</td>
                          <td class="px-1">{{ citem.variant ? formatVariantLabel(citem.variant) : "No Variant" }}</td>


                          <td class="px-1 text-right">
                            <input
                              type="number"
                              v-model="citem.unit_price"
                              @input="updateCourtesyPrice(index)"
                              style="width: 80px"
                            />
                          </td>
                          <td class="px-1">
                            <!-- {{ item.quantity }} -->
                            <input
                              type="number"
                              v-model="citem.courtesy_quantity"
                              @input="updateCourtesyQuantity(index)"
                              style="width: 50px"
                            />
                          </td>
                          <td class="text-right px-1">
                            {{ citem.unit_price * citem.courtesy_quantity }}
                          </td>
                          <td class="text-center px-1">
                            <a href="#" @click="removeFromCourtesyCart(index, citem.variant)"
                              ><i class="fa fa-trash text-danger"></i
                            ></a>
                          </td>
                        </tr>
                        <tr>
                          <td class="fw-bold px-1" colspan="3">TOTAL</td>
                          <td class="fw-bold px-1"></td>
                          <td class="fw-bold px-1 text-right">
                            {{ calculateCourtesyTotal() }}
                          </td>
                          <td class="fw-bold px-1"></td>
                        </tr>
                      </tbody>
                      <tbody v-else>
                        <tr>
                          <td colspan="6" class="py-3 text-center">
                            <div v-if="!isNew && !purchase">
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
                      class="form-control form-control-lg col-md-8"
                      placeholder="Choose..."
                      name="attach_file"
                      @change="onFileChange"
                      accept="image/*,application/pdf"
                      :class="{ 'is-invalid': form.errors.has('attach_file') }"
                    />
                    <HasError :form="form" field="attach_file" />
                    <p>[Allow File type:jpeg,png,jpg,gif,svg,pdf & Max Size:2MB]</p>
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
                        >Purchase Note:
                        <div class="text-danger"></div
                      ></label>
                    </div>
                    <textarea
                      name=""
                      id=""
                      cols="30"
                      rows="2"
                      class="form-control"
                      v-model="form.purchase_note"
                      >{{ form.purchase_note }}</textarea
                    >
                  </div>
                  <div class="form-group mt-2">
                    <!-- <div v-if="form.progress">Progress: {{ form.progress.percentage }}%</div> -->
                    <router-link 
                          to="/purchases"
                          class="btn btn-lg btn-primary px-2 mx-1"
                          ><i class="fa fa-list"></i> Manage Purchase</router-link
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
import { reactive } from "vue";
import SearchResult from "./SearchResult.vue";

import { mapActions } from "vuex";
export default {
  components: { SearchResult },
  setup() {
    const cartStatus = reactive({}); // Vue 3 reactive object
    return { cartStatus };
  },
  data() {
    return {
      isSubmitting: false,
      isLoading: false,
      imageUrl: null,
      purchase: false,
      publicPath: window.publicPath,
      fileExtension: null,
      paginator: {
        totalRecords: 0,
        from: 0,
        to: 0,
        current_page: "",
        per_page: "",
      },
      suppliers: [],
      payment_methods: [],
      books: {
        type: Object,
        default: null,
      },
      form: new Form({
        supplier_id: "",
        purchase_date: "",
        // cartItems: [],
        total_amount: 0,
        courtesy_total_amount: 0,
        discount_percentage: "",
        discount_amount: "",
        vat_percentage: "",
        vat_amount: 0,
        net_amount: 0,
        pay_amount: 0,
        due_amount: 0,
        paid_by: "",
        payment_method: "",
        payment_description: "",
        purchase_note: "",
        attach_file: null,
      }),
      params: {
        paginate: 5,
        id: "",
        title: "",
        category_id: "",
        sort_field: "created_at",
        sort_direction: "desc",
      },
      search: "",
      searchResults: [],
      selectedVariants: {},
      cartItems: [],
      courtesyCartItems: [],
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
      if (val.length >= 2 || old.length >= 2) {
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
    this.suppliers = this.$store.getters.getSuppliers;
    if (this.suppliers.length == 0) {
      const response = await axios.get("/api/get-suppliers");
      this.suppliers = response.data;
    }
    this.payment_methods = this.$store.getters.getPaymentMethods;
    if (this.payment_methods.length == 0) {
      const response = await axios.get("/api/get-payment-methods");
      this.payment_methods = response.data;
    }
    if (!this.isNew) {
      const response = await axios.get(`/api/purchases/${this.$route.params.id}`);

      this.purchase = true;
      const purchase = response.data.purchase;
      this.form.supplier_id = purchase.supplier_id;
      this.form.purchase_date = purchase.purchase_date;
      this.form.discount_percentage = purchase.discount_percentage;
      this.form.discount_amount = purchase.discount_amount;
      this.form.vat_percentage = purchase.vat_percentage;
      this.form.vat_amount = purchase.vat_amount;
      this.form.net_amount = purchase.net_amount;
      this.form.pay_amount = purchase.pay_amount;
      this.form.due_amount = purchase.due_amount;
      this.form.purchase_note = purchase.purchase_note;
      this.form.attach_file = purchase.attach_file;

      if (response.data.payment_details.length > 0) {
        this.form.payment_method = response.data.payment_details[0].payment_method;
        this.form.paid_by = response.data.payment_details[0].paid_by;
        this.form.payment_description =
          response.data.payment_details[0].payment_description;
      }
      

      this.imageUrl =
        `${window.publicPath}assets/img/purchase/` + response.data.purchase.attach_file;

      const fileName = response.data.purchase.attach_file;
      if (fileName) {
        const parts = fileName.split(".");
        if (parts.length > 1) {
          // Get the last part as the file extension
          this.fileExtension = parts[parts.length - 1].toLowerCase();
        }
      }

      this.cartItems = response.data.purchase_regular_details;
      this.courtesyCartItems = response.data.purchase_courtesy_details;
    }else{     
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
    removeSingleImage(image, index) {
      this.imageUrl = null;
      this.form.attach_file = null;
    },
    async getBooks(page = 1) {
        // if (this.search.length > 2) {
        //     const response = await axios.get(`/api/books/search`, {
        //     params: { query: this.searchQuery },
        //     });
        //     this.searchResults = response.data;
        // } else {
        //     this.searchResults = [];
        // }
      this.isLoading = true;
      await axios
        .get("/api/books", {
          params: {
            page,
            search: this.search.length >= 2 ? this.search : "",
            ...this.params,
          },
        })
        .then((response) => {
          this.searchResults = response.data.data;
          this.isLoading = false;
          this.books = response.data;
          this.paginator.totalRecords = response.data.total;
          
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
    formatVariantLabel(variant) {
      if (!variant || !variant.attribute_options) return "No attributes";
      return variant.attribute_options.map(opt => `${opt.attribute.name}: ${opt.value}`).join(", ");
    },
    getCatWiseBook(categoryId) {
      this.params.category_id = categoryId;
    },
    handleAddToCart({ book, variant, cartType }) {
      const cartLabel = cartType === 'purchase' ? 'cart' : 'courtesy';
      const variantId = variant ? variant.id : "no-variant";
      const cartKey = `${book.id}-${variantId}-${cartType}`;
      //alert(cartKey);
      document.querySelector(`.addToCart${cartKey}`).innerHTML = `<i class="fa fa-check"></i> Added to ${cartLabel}`;
      
      if (cartType === "purchase") {
        // this.directPurchaseCart.push(item);
        this.addToPurchaseCart(book, variant)
      } else if (cartType === "courtesy") {
        this.addToCourtesyCart(book, variant)
        // this.courtesyCart.push(item);
      }
    },
    addToPurchaseCart(book, variant = null) {
      const variantId = variant ? variant.id : "no-variant";
      const cartItem = this.cartItems.find(
        (item) => item.id === book.id && item.variant_id === variantId
      );
      if (cartItem) {
        Notification.success(`Item '${cartItem.title}' Qty. has been updated!`);
        cartItem.quantity++; // If the product already exists, increment its quantity
      } else {
        Notification.success(`Purchase Item Added!`);
        book.discount_amount = 0; // Initialize discount
        
        const newItem = { ...book, quantity: 1, variant_id: variantId };
        if (variant) {
          newItem.variant = variant;
        }
        this.cartItems.push(newItem);
      }
      // this.calculateTotal();
    },
    addToCourtesyCart(book, variant = null) {
      const variantId = variant ? variant.id : "no-variant";

      const cartItem = this.courtesyCartItems.find(
        (item) => item.id === book.id && item.variant_id === variantId
      );

      if (cartItem) {
        Notification.success(`Item '${cartItem.title}' Qty. has been updated!`);
        cartItem.courtesy_quantity++;
      } else {
        Notification.success(`Courtesy Item Added!`);
        book.discount_amount = 0; // Initialize discount
        const newItem = { ...book, courtesy_quantity: 1, unit_price: book.price, variant_id: variantId };

        if (variant) {
          newItem.variant = variant;
        }
        this.courtesyCartItems.push(newItem);
      }
      // this.calculateTotal();
    },
    removeFromCart(index, variant,cartType = 'purchase') {
      if (index === -1 || !this.cartItems[index]) return; // Ensure the item exists
      
      const cartItem = this.cartItems[index];
      
      const variantId = variant ? variant.id : "no-variant";
      const bookId = cartItem.id ? cartItem.id : "no-book";  // Safeguard for book
      const cartKey = `${bookId}-${variantId}-${cartType}`;
      
      document.querySelector(`.addToCart${cartKey}`).innerHTML = `<i class="fa fa-check"></i> Add to cart`;
      
      // Remove the item from the cart array
      this.cartItems.splice(index, 1);

      // Update UI (you should consider using Vue's reactivity here instead of direct DOM manipulation)
      Notification.success("Item Removed!");
    },

    removeFromCourtesyCart(index, variant, cartType = 'courtesy') {
      if (index === -1 || !this.courtesyCartItems[index]) return; // Ensure the item exists
      
      const cartItem = this.courtesyCartItems[index];
      
      const variantId = variant ? variant.id : "no-variant";
      const bookId = cartItem.id ? cartItem.id : "no-book";  // Safeguard for book
      const cartKey = `${bookId}-${variantId}-${cartType}`;
   
      document.querySelector(`.addToCart${cartKey}`).innerHTML = '<i class="fa fa-plus"></i> সৌজন্য কপি';
      // Remove the item from the cart array
      this.courtesyCartItems.splice(index, 1);
      Notification.success(`Item removed from courtesy cart!`);
      
    },
    updatePrice(index) {
      // Ensure price is a positive number
      if (this.cartItems[index].price < 0) {
        this.cartItems[index].price = 0;
      }
    },
    updateCourtesyPrice(index) {
      // Ensure price is a positive number
      if (this.courtesyCartItems[index].unit_price < 0) {
        this.courtesyCartItems[index].unit_price = 0;
      }
    },
    updateQuantity(index) {
      // Ensure quantity is a positive number
      if (this.cartItems[index].quantity < 1) {
        this.cartItems[index].quantity = 1;
      }
    },
    calculateSubTotal(item) {
      let price = item.unit_price || item.price;
      let quantity = item.courtesy_quantity || item.quantity;
      let discount_amount = item.discount_amount || 0;
      return (price * quantity) - discount_amount;
    },
    updateTotal(item) {
      item.total = this.calculateSubTotal(item);
    },
    updateCourtesyQuantity(index) {
      // Ensure quantity is a positive number
      if (this.courtesyCartItems[index].courtesy_quantity < 1) {
        this.courtesyCartItems[index].courtesy_quantity = 1;
      }
    },
    updateDiscount() {
      this.form.discount_percentage = Math.min(100, Math.max(0, Number(this.form.discount_percentage) || 0));
    },
    updateVat() {
      this.form.vat_percentage = Math.min(100, Math.max(0, Number(this.form.vat_percentage) || 0));
    },
    updatePayAmount() {
      const netTotal = this.calculateNetTotal();
      this.form.pay_amount = Math.min(netTotal, Math.max(0, Number(this.form.pay_amount) || 0));
    },
    calculateDiscountAmount() {
      const totalBeforeDiscount = this.calculateTotal();
      const discountAmount = (totalBeforeDiscount * this.form.discount_percentage) / 100;
      this.form.discount_amount = discountAmount;
      return discountAmount.toFixed(2);
    },
    calculateVatAmount() {
      const totalBeforeDiscount = this.calculateTotal();
      const vatPercentage = Number(this.form.vat_percentage) || 0;
      // Ensure vat percentage is within 0-100 range
      this.form.vat_percentage = Math.min(100, Math.max(0, vatPercentage));

      const vatAmount = (totalBeforeDiscount * this.form.vat_percentage) / 100;
      this.form.vat_amount = parseFloat(vatAmount.toFixed(2)); // Ensures number format

      return this.form.vat_amount;
    },
    calculateTotal() {
      return this.cartItems.reduce((total, item) => {
        const discountAmount = Number(item.discount_amount) || 0;
        const res = total + item.price * item.quantity - discountAmount;
        this.form.total_amount = res;
        return res;
      }, 0);
    },
    calculateCourtesyTotal() {
      return this.courtesyCartItems.reduce((total, item) => {
        const res = total + item.unit_price * item.courtesy_quantity;
        this.form.courtesy_total_amount = res;
        return res;
      }, 0);
    },
    calculateNetTotal() {
      const totalBeforeDiscount = this.calculateTotal();
      const discountPercentage = Number(this.form.discount_percentage) || 0;
      const discountAmount = (totalBeforeDiscount * discountPercentage) / 100;
      const totalAfterDiscount = totalBeforeDiscount - discountAmount;
      const vatAmount = (totalAfterDiscount * this.form.vat_percentage) / 100;
      const totalWithVAT = totalAfterDiscount + vatAmount;
      this.form.net_amount = totalWithVAT;
      return totalWithVAT.toFixed(2);
    },
    dueAmount() {
      const netAmount = this.calculateNetTotal();
      const payAmount = Number(this.form.pay_amount) || 0;
      const dueAmount = netAmount - payAmount;
      this.form.due_amount = dueAmount;
      return dueAmount.toFixed(2);
    },
    async submitForm() {
      this.isSubmitting = true;
      console.log(this.form);
      // console.log(this.form.cartItems);
      if (this.isNew) {
        try {
          const payload = {
            ...this.form, // Spread other form fields
            cart_items: this.cartItems,
            courtesy_cart_items: this.courtesyCartItems,
          };

          // Make the POST request with the correct data structure
          await axios.post("/api/purchases", payload);

          // Navigate and notify
          this.$router.push({ name: "purchases" });
          Notification.success("Created purchase successfully!");
        } catch (error) {
          // Log error response to debug
          if (error.response && error.response.data) {
            console.error("Error:", error.response.data.errors);
            Notification.error("Failed to create purchase. Check your input.");
          } else {
            console.error("Unexpected error:", error);
            Notification.error("An unexpected error occurred.");
          }
        } finally {
          this.isSubmitting = false;
        }
      } else {
        try {
          await this.form
            .post(`/api/purchases/${this.$route.params.id}`, {
              params: {
                cart_items: this.cartItems,
                courtesy_cart_items: this.courtesyCartItems,
                ...this.form,
              },
            })
            .then((response) => {
              Notification.success("purchase info updated");
              this.$router.push("/purchases");
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
  },
};
</script>

<style type="text/css" scoped>
ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
}
</style>
