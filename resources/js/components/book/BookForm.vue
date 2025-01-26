<template>
  <div class="row">
    <div class="col-md-12 offset-md-0">
      <div class="card shadow-sm my-2">
        <div class="card-header py-2 my-bg-success">
          <h3 class="text-white-900" v-if="isNew"><i class="fa fa-plus"></i> Add Book (বই যোগ করুন)</h3>
          <h3 class="text-white-900" v-else><i class="fa fa-pencil"></i> Edit Book</h3>
          <p class="text-white m-0">ফরমের লাল তারকা (<span class="text-danger">*</span>) চিহ্নিত ঘরগুলো অবশ্যই পূরণ করুন। অন্যান্য ঘরগুলো পূরণ ঐচ্ছিক।</p>
        </div>
        <div class="card-body p-3">
          <div class="form">
            <form
              id="form"
              class="book"
              enctype="multipart/form-data"
              @submit.prevent="submitForm"
              @keydown="form.onKeydown($event)"
            >
              <AlertError :form="form" />
              <div v-if="!isNew && !book">
                <LoadingSpinner />
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-4 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Book Title
                        <div class="text-danger">*</div></label
                      >
                    </div>
                    <input
                      type="text"
                      class="form-control"
                      autocomplete="off"
                      placeholder="Enter Your book title"
                      name="title"
                      v-model="form.title"
                      :class="{ 'is-invalid': form.errors.has('title') }"
                    />
                    <HasError :form="form" field="title" />
                  </div>
                  <div class="mb-2 row pe-4">
                    <multiselect
                      v-model="form.selected_authors"
                      :options="authors"
                      :multiple="true"
                      track-by="id"
                      label="author_name"
                      placeholder="Select authors"
                    />
                    <HasError :form="form" field="selected_authors" />
                  </div>
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-4 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Category</label
                      >
                    </div>
                    <select
                      class="custom-select mx-0 pe-0"
                      v-model="form.category_id"
                      @change="getSubCategories"
                      :class="{ 'is-invalid': form.errors.has('category_id') }"
                    >
                      <option value="" disabled selected>Choose...</option>
                      <option
                        :value="category.id"
                        v-for="category in categories"
                        :key="categories.id"
                      >
                        {{ category.category_name }}
                      </option>
                    </select>
                    <HasError :form="form" field="category_id" />
                  </div>
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-4 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Genre
                      </label>
                    </div>
                    <input
                      type="text"
                      class="form-control"
                      autocomplete="off"
                      placeholder="Enter Your book genre"
                      name="genre"
                      v-model="form.genre"
                      :class="{ 'is-invalid': form.errors.has('genre') }"
                    />
                    <HasError :form="form" field="genre" />
                  </div>
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-5 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Buying Discount Percentage
                        <div class="text-danger">*</div></label
                      >
                    </div>
                    <input
                      type="text"
                      class="form-control"
                      autocomplete="off"
                      placeholder="Ex. 25"
                      name="buying_discount_percentage"
                      v-model="form.buying_discount_percentage"
                      :class="{
                        'is-invalid': form.errors.has('buying_discount_percentage'),
                      }"
                    />
                    <HasError :form="form" field="buying_discount_percentage" />
                  </div>
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-5 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Buying Vat Percentage
                        <div class="text-danger">*</div></label
                      >
                    </div>
                    <input
                      type="text"
                      class="form-control"
                      autocomplete="off"
                      placeholder="Ex: 5"
                      name="buying_vat_percentage"
                      v-model="form.buying_vat_percentage"
                      :class="{ 'is-invalid': form.errors.has('buying_vat_percentage') }"
                    />
                    <HasError :form="form" field="buying_vat_percentage" />
                  </div>
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-5 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Publication Year
                        <div class="text-danger">*</div></label
                      >
                    </div>
                    <input
                      type="text"
                      class="form-control"
                      autocomplete="off"
                      placeholder="Ex: 2023"
                      name="publication_year"
                      v-model="form.publication_year"
                      :class="{ 'is-invalid': form.errors.has('publication_year') }"
                    />
                    <HasError :form="form" field="publication_year" />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-4 mx-0">
                      <label
                        class="input-group-text col-md-12"
                        for="inputGroupSelect01"
                        title="(International Standard Book Number)"
                        >ISBN
                        <div class="text-danger"></div></label
                      >
                    </div>
                    <input
                      type="text"
                      class="form-control"
                      autocomplete="off"
                      placeholder="Enter Your book isbn"
                      name="isbn"
                      v-model="form.isbn"
                      :class="{ 'is-invalid': form.errors.has('isbn') }"
                    />
                    <HasError :form="form" field="isbn" />
                  </div>

                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-4 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Publisher
                        <div class="text-danger">*</div></label
                      >
                    </div>
                    <select
                      class="custom-select mx-0 pe-0"
                      v-model="form.publisher_id"
                      :class="{ 'is-invalid': form.errors.has('publisher_id') }"
                    >
                      <option value="" disabled selected>Choose...</option>
                      <option
                        :value="publisher.id"
                        v-for="publisher in publishers"
                        :key="publisher.id"
                      >
                        {{ publisher.publisher_name }}
                      </option>
                    </select>
                    <HasError :form="form" field="publisher_id" />
                  </div>

                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-4 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Sub-category</label
                      >
                    </div>
                    <select
                      class="custom-select mx-0 pe-0"
                      v-model="form.sub_category_id"
                      :class="{ 'is-invalid': form.errors.has('sub_category_id') }"
                    >
                      <option value="" disabled selected>Choose...</option>
                      <option
                        :value="sub_category.id"
                        v-for="sub_category in sub_categories"
                        :key="sub_category.id"
                      >
                        {{ sub_category.sub_category_name }}
                      </option>
                    </select>
                    <HasError :form="form" field="sub_category_id" />
                  </div>
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-4 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Price
                        <div class="text-danger">*</div></label
                      >
                    </div>
                    <input
                      type="text"
                      class="form-control"
                      autocomplete="off"
                      placeholder="Enter Your book price"
                      name="price"
                      v-model="form.price"
                      :class="{ 'is-invalid': form.errors.has('price') }"
                    />
                    <HasError :form="form" field="price" />
                  </div>
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-5 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Selling Discount Percentage
                        <div class="text-danger">*</div></label
                      >
                    </div>
                    <input
                      type="text"
                      class="form-control"
                      autocomplete="off"
                      placeholder="Ex: 15"
                      name="selling_discount_percentage"
                      v-model="form.selling_discount_percentage"
                      :class="{
                        'is-invalid': form.errors.has('selling_discount_percentage'),
                      }"
                    />
                    <HasError :form="form" field="selling_discount_percentage" />
                  </div>
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-5 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Selling Vat Percentage
                        <div class="text-danger">*</div></label
                      >
                    </div>
                    <input
                      type="text"
                      class="form-control"
                      autocomplete="off"
                      placeholder="Ex: 7"
                      name="selling_vat_percentage"
                      v-model="form.selling_vat_percentage"
                      :class="{ 'is-invalid': form.errors.has('selling_vat_percentage') }"
                    />
                    <HasError :form="form" field="selling_vat_percentage" />
                  </div>
                  <div class="input-group mb-2 row mx-0 px-0">
                    <div class="input-group-prepend px-0 col-md-5 mx-0">
                      <label class="input-group-text col-md-12" for="inputGroupSelect01"
                        >Book Photo
                        <div class="text-danger"></div
                      ></label>
                    </div>
                    <input
                      type="file"
                      class="form-control"
                      placeholder="Enter Your book Photo"
                      name="photo"
                      @change="onFileChange"
                      accept="image/*"
                      :class="{ 'is-invalid': form.errors.has('photo') }"
                    />
                    <HasError :form="form" field="photo" />
                    [Allow File type:jpeg,png,jpg,gif,svg & Max Size:2MB]
                    <div class="image-item">
                      <img
                        v-if="imageUrl"
                        :src="imageUrl"
                        alt="Image Preview"
                        width="150"
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
                  </div>
                </div>
              </div>

              <hr />
              <div class="form-group">
                <!-- <div v-if="form.progress">Progress: {{ form.progress.percentage }}%</div> -->
                <router-link to="/books" class="btn btn-lg btn-outline-primary"> &lt;&lt; Manage Book </router-link>
                <save-button v-if="isNew" :is-submitting="isSubmitting"></save-button>
                <save-changes-button
                  v-else
                  :is-submitting="isSubmitting"
                ></save-changes-button>
                <reset-button @reset-data="resetData" />

              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script type="text/javascript">
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";

import { mapActions } from "vuex";
export default {
components: {
    Multiselect
  },
  data() {
    return {
      myValue: '',
      myOptions: ['op1', 'op2', 'op3'],
      isSubmitting: false,
      imageUrl: null,
      book: false,
      sub_categories: [],
      authors: [], // List of all authors (fetched from API)
      publishers: [],
      form: new Form({
        title: "",
        isbn: "",
        genre: "",
        price: "",
        selected_authors: [],
        publisher_id: "",
        category_id: "",
        sub_category_id: "",
        photo: null,
        buying_discount_percentage: "",
        selling_discount_percentage: "",
        buying_vat_percentage: "",
        selling_vat_percentage: "",
        publication_year: "",
      }),
    };
  },
  computed: {
    isNew() {
      return !this.$route.path.includes("edit");
    },
    categories() {
      return this.$store.state.categories;
    },
  },
  async created() {
    this.fetchCategories();
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
    if (!this.isNew) {
      const response = await axios.get(`/api/books/${this.$route.params.id}`);

      // console.log(response.data);

      this.form.title = response.data.title;
      this.form.isbn = response.data.isbn;
      this.form.photo = response.data.photo;
      this.form.selected_authors = response.data.authors;
      this.form.publisher_id = response.data.publisher_id;
      this.form.category_id = response.data.category_id;
      if(response.data.category_id){
        this.getSubCategories()
      }
      this.form.sub_category_id = response.data.sub_category_id ? response.data.sub_category_id : "";
      this.form.price = response.data.price;
      this.form.publication_year = response.data.publication_year;
      this.form.buying_discount_percentage = response.data.buying_discount_percentage;
      this.form.buying_vat_percentage = response.data.buying_vat_percentage;
      this.form.selling_discount_percentage = response.data.selling_discount_percentage;
      this.form.selling_vat_percentage = response.data.selling_vat_percentage;
      this.form.genere = response.data.genere;
      this.imageUrl =
        `${window.publicPath}assets/img/book/thumbnail/` + response.data.photo;
      this.book = true;
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
          this.form.errors.set(
            "photo",
            "File size exceeds the maximum allowed size."
          );
          Notification.error("File size exceeds the maximum allowed size.");
        }
        if (!allowedFileTypes.includes(selectedFile.type)) {
          this.form.errors.set(
            "photo",
            " File type is not supported. Please choose a valid file type."
          );
          Notification.error(
            " File type is not supported. Please choose a valid file type."
          );
        }
        this.form.photo = selectedFile;

        const reader = new FileReader();

        reader.onload = (e) => {
          this.imageUrl = e.target.result;
        };
        reader.readAsDataURL(selectedFile);
      }
    },
    removeSingleImage(image, index) {
      this.imageUrl = null;
      this.form.photo = null;
    },
    async submitForm() {
      this.isSubmitting = true;
      this.form.selected_authors = this.form.selected_authors.map((author) => author.id);
      if (this.isNew) {
        
        await this.form
          .post("/api/books", this.form)
          .then(() => {
            this.$router.push({ name: "books" });
            Notification.success(`Create book ${this.form.name} successfully!`);
          })
          .catch((error) => {
            // console.log(error);
            if (error.response.status === 422) {
              this.errors = error.response.data.errors;
              Notification.error("Validation Errors!");
            } else if (error.response.status === 401) {
              // statusText = "Unbookized";
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
            .post(`/api/books/${this.$route.params.id}`, {
              ...this.form,
              
            })
            .then((response) => {
              Notification.success("book info updated");
              this.$router.push("/books");
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
    getSubCategories() {
      axios.get("/api/get-category-wise-sub-categories", {
          params: {
            category_id: this.form.category_id,
          },
        })
        .then((response) => {
          this.sub_categories = response.data;
        });
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
