<template>
    <div class="mt-3">
        <div v-if="searchResults.length" class="">
            <div class="table-responsive">
                <table
                    class="table table-sm rounded-3 mb-2"
                    v-for="book in searchResults"
                    :key="book.id"
                    style="background-color: #f1f0e8"
                >
                    <tr>
                        <td
                            colspan="6"
                            style="background: #ada991; color: #fff"
                        >
                            <h4 style="background: #ada991; color: #fff">
                                {{ book.title }}
                                <span style="font-size: 14px">
                                    Author:
                                    <span
                                        v-for="(author, index) in book.authors"
                                        :key="author.id"
                                    >
                                        <!-- <i class="fa fa-pen"></i> -->
                                        {{ author.author_name
                                        }}<span
                                            v-if="
                                                index < book.authors.length - 1
                                            "
                                            >,
                                        </span>
                                    </span>
                                    ; Publication:
                                    {{ book.publisher.publisher_name }}
                                </span>
                                <span
                                    v-if="book.variants.length == 0"
                                    style="font-size: 14px"
                                >
                                    <em
                                        >(BDT {{ book.price }},
                                        {{ book.stock_quantity }} book(s)
                                        available)</em
                                    >
                                    <button
                                        @click="
                                            addToCart(book, null, 'courtesy')
                                        "
                                        :class="[
                                            'addToCart',
                                            `addToCart${book.id}-no-variant-courtesy`,
                                        ]"
                                        class="btn btn-sm m-1 my-btn-danger float-end"
                                    >
                                        <i class="fa fa-plus"></i> সৌজন্য কপি
                                    </button>
                                    <button
                                        @click="
                                            addToCart(book, null, 'purchase')
                                        "
                                        :class="[
                                            'addToCart',
                                            `addToCart${book.id}-no-variant-purchase`,
                                        ]"
                                        class="btn btn-sm m-1 my-btn-primary float-end"
                                    >
                                        <i class="fa fa-plus"></i> Add to Cart
                                    </button>
                                </span>
                            </h4>
                        </td>
                    </tr>
                    <tr v-if="book.variants.length">
                        <th class="text-nowrap" colspan="3">
                            Variant Attributes
                        </th>
                        <th class="text-right">Price</th>
                        <th>Stock</th>
                        <!-- <th>Quantity</th> -->
                        <th colspan="2"></th>
                    </tr>
                    <tr
                        v-for="(variant, index) in book.variants"
                        :key="variant.id"
                        class="py-2 border-3 border-bottom border-white"
                    >
                        <!-- <td class="text-nowrap" style="max-width: 10px!important;"><i class="fas fa-long-arrow-alt-right  bg-transparent"> </i></td> -->
                        <td class="py-2 bg-transparent" colspan="3">
                            <!-- <span class="text-info ms-1" v-html="formatVariantLabel(variant)"></span> -->
                            <ul class="py-2 bg-transparent">
                                <li
                                    v-for="opt in variant.attribute_options"
                                    :key="opt.id"
                                >
                                    <b>{{ opt.attribute.name }}</b
                                    >: {{ opt.value }}
                                </li>
                            </ul>
                        </td>
                        <td class="py-2 text-right">{{ variant.price }}</td>
                        <td class="py-2" style="max-width: 20px !important">
                            {{ variant.stock_quantity }}
                        </td>
                        <!-- <td class="py-2"><input type="number" v-model="quantities[variant.id]" min="1" value="1" class="form-control border-1" placeholder="Qty." /></td> -->
                        <td class="py-2 text-nowrap float-end" colspan="2">
                            <button
                                @click="addToCart(book, variant, 'purchase')"
                                :class="[
                                    'addToCart',
                                    `addToCart${book.id}-${variant.id}-purchase`,
                                ]"
                                class="btn btn-sm m-1 my-btn-primary"
                            >
                                <i class="fa fa-plus"></i>
                                Add to Cart
                            </button>
                            <button
                                @click="addToCart(book, variant, 'courtesy')"
                                :class="[
                                    'addToCart',
                                    `addToCart${book.id}-${variant.id}-courtesy`,
                                ]"
                                class="btn btn-sm m-1 my-btn-danger"
                            >
                                <i class="fa fa-plus"></i>
                                Add to C Cart
                                <!-- সৌজন্য কপি -->
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ["searchResults", "cartStatus"],
    emits: ["add-to-cart"],
    data() {
        return {
            selectedVariants: {},
            quantities: {},
        };
    },
    methods: {
        // formatVariantLabel(variant) {
        //   if (!variant.attribute_options || variant.attribute_options.length === 0) {
        //     return `No attributes`;
        //   }
        //   return variant.attribute_options
        //     .map(opt => `<strong>${opt.attribute.name}</strong>: <em>${opt.value}</em>`)
        //     .join(", ");
        // },
        addToCart(book, variant, cartType) {
            // Emit event with book and variant (if available)
            this.$emit("add-to-cart", { book, variant, cartType });
        },
    },
};
</script>
<style scoped>
.text-info {
    color: #493d9e !important;
    background-color: transparent !important;
}
</style>
