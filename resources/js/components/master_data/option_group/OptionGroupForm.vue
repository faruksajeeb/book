<template>
  <div>
    <div class="row">
      <div class="col-md-6 offset-md-3">
        <div class="card shadow-sm my-4">
          <div class="card-header py-2 my-bg-success">
            <h3 class="text-white-900" v-if="isNew">
              <i class="fa fa-plus"></i> Add Option Group
            </h3>
            <h3 class="text-white-900" v-else>
              <i class="fa fa-pencil"></i> Edit Option Group
            </h3>
          </div>
          <div class="card-body p-3">
            <div class="form">
              <form
                class="user"
                @submit.prevent="submitForm"
                @keydown="form.onKeydown($event)"
              >
                <AlertError :form="form" />
                <div class="form-group">
                  <div class="form-row">
                    <div class="col-md-12">
                      <label for=""
                        >Option Group Name <span class="text-danger">*</span></label
                      >
                      <input
                        type="text"
                        class="form-control"
                        id="exampleInputFirstName"
                        placeholder="Enter Your optionGroup Name"
                        v-model="form.name"
                        :class="{ 'is-invalid': form.errors.has('name') }"
                      />
                      <HasError :form="form" field="name" />
                    </div>
                  </div>
                </div>
                <hr />
                <div class="form-group">
                  <router-link to="/option-groups"> Manage Option Group </router-link>
                  <save-button :is-submitting="isSubmitting"></save-button>
                  <reset-button @reset-data="resetData" />
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script type="text/javascript">
// import {Form} from "vform";
export default {
  data: () => ({
    isSubmitting: false,
    option_group: null,
    permissions: [],
    form: new Form({
      name: "",
    }),
  }),
  computed: {
    isNew() {
      return !this.$route.path.includes("edit");
    },
  },
  async created() {
    if (!User.loggedIn()) {
      this.$router.push("/");
    }
    if (!this.isNew) {
      const response = await axios.get(`/api/option-groups/${this.$route.params.id}`);
      // alert(response.data);
      this.form.name = response.data.name;
    }
  },
  methods: {
    async submitForm() {
      this.isSubmitting = true;
      if (this.isNew) {
        await this.form
          .post("/api/option-groups", this.form)
          .then(() => {
            this.$router.push({ name: "option-groups" });
            Notification.success(`Create Option Group ${this.form.name} successfully!`);
          })
          .catch((error) => {
            // console.log(error);
            if (error.response.status === 422) {
              this.errors = error.response.data.errors;
              Notification.error("Validation Errors!");
            } else if (error.response.status === 401) {
              // statusText = "Unauthorized";
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
          console.log(this.form);
          await this.form
            .put(`/api/option-groups/${this.$route.params.id}`, this.form)
            .then((response) => {
              Notification.success("option Group info Updated");
              this.$router.push("/option-groups");
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
