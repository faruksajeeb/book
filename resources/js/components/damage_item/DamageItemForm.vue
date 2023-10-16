<template>
  <div class="row">
    <div class="col-md-6 offset-md-3">
      <div class="card shadow-sm my-2">
        <div class="card-header py-2 my-bg-success">
          <h3 class="text-white-900" v-if="isNew"><i class="fa fa-plus"></i> Add Damage Item </h3>
          <h3 class="text-white-900" v-else><i class="fa fa-pencil"></i> Edit Damange Item</h3>
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
              <div v-if="!isNew && !banageItem">
                <LoadingSpinner />
              </div>
              <div class="input-group mb-2 row mx-0 px-0">
                  <div class="input-group-prepend px-0 col-md-4 mx-0">
                    <label
                      class="input-group-text col-md-12"
                      for="inputGroupSelect01"
                      title=""
                      >Damage Date
                      <div class="text-danger">**</div></label
                    >
                  </div>
                  <input
                    type="text"
                    class="form-control datecalender"
                    id="datecalander"
                    autocomplete="off"
                    placeholder="Choose damage date"
                    name="damage_date"
                    v-model="form.damage_date"
                    @input="clearError('damage_date')"
                    :class="{ 'is-invalid': form.errors.has('damage_date') }"
                  />
                  <HasError :form="form" field="damage_date" />
                </div>
              <div class="row">
                <div class="form-group">
                  <label for="">Damage Item <span class="text-danger">**</span></label>
                  <select name="" id="" :class="{ 'is-invalid': form.errors.has('book_id') }"  v-model="form.book_id" class="form-select">
                    <option value="" selected>--select item--</option>
                    <option :value="item.id" v-for="item in books">{{ item.title }}</option>
                  </select>
                  <HasError :form="form" field="damage_date" />
                </div>
                <div class="form-group">
                  <label for="">Damage Quantity <span class="text-danger">**</span> </label>
                  <input type="number"  :class="{ 'is-invalid': form.errors.has('quantity') }" v-model="form.quantity" class="form-control"/>
                  <HasError :form="form" field="quantity" />
                </div>
              </div>

              <hr />
              <div class="form-group">
                <!-- <div v-if="form.progress">Progress: {{ form.progress.percentage }}%</div> -->
                <router-link to="/damage-items" class="btn btn-lg btn-outline-primary"> &lt;&lt; Damage Items </router-link>
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
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";

import { mapActions } from "vuex";
export default {
    components: {
  
  },
  data() {
    return {
         
      isSubmitting: false,
      imageUrl: null,
      book: false,
      form: new Form({
        damage_date: "",
        book_id: "",
        quantity: ""
      }),
    };
  },
  computed: {
    isNew() {
      return !this.$route.path.includes("edit");
    },
    books() {
      return this.$store.state.books;
    },
  },
  mounted() {
    flatpickr("#datecalander", {
      dateFormat: "Y-m-d", // Customize the date format as needed
      // Add more Flatpickr options as needed
    });
  },
  async created() {
    this.fetchBooks();
    
    if (!this.isNew) {
      const response = await axios.get(`/api/damage-items/${this.$route.params.id}`);
      this.form.book_id = response.data.book_id;
      this.form.damage_date = response.data.damage_date;
      this.form.quantity = response.data.quantity;
      this.banageItem = true;
    }
  },
  methods: {
    ...mapActions(["fetchBooks"]),
 
    async submitForm() {
      this.isSubmitting = true;
      if (this.isNew) {
        await this.form
          .post("/api/damage-items", this.form)
          .then(() => {
            this.$router.push({ name: "damage-items" });
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
            .post(`/api/damage-items/${this.$route.params.id}`, this.form)
            .then((response) => {
              Notification.success("book info updated");
              this.$router.push("/damage-items");
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