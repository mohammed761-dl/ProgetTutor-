<template>
  <Head title="Show PO" />

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <div class="mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              Purchase Order Details
            </h2>
          </div>

          <div class="mb-6">
            <div class="flex justify-between items-center">
              <div>
                <p><strong>PO Number:</strong> {{ purchaseOrder.po_number }}</p>
                <p><strong>Date:</strong> {{ purchaseOrder.created_at }}</p>
              </div>
              <div class="space-x-2">
                <Link
                  :href="route(`purchase-orders.edit`, purchaseOrder.id)"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                >
                  Edit
                </Link>
                <Link
                  :href="route(`purchase-orders.download-pdf`, purchaseOrder.id)"
                  class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                >
                  Download PDF
                </Link>
              </div>
            </div>
          </div>

          <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Customer Information</h3>
            <p><strong>Name:</strong> {{ purchaseOrder.customer.name }}</p>
            <p><strong>Email:</strong> {{ purchaseOrder.customer.email }}</p>
            <p><strong>Address:</strong> {{ purchaseOrder.customer.address }}</p>
          </div>

          <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Products</h3>
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="product in purchaseOrder.po_products" :key="product.id">
                  <td class="px-6 py-4 whitespace-nowrap">{{ product.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ product.quantity }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(product.price) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(product.quantity * product.price) }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="px-6 py-4 text-right font-bold">Total:</td>
                  <td class="px-6 py-4">{{ formatCurrency(calculateTotal()) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="mt-6">
            <Link
              :href="route(`purchase-orders.index`)"
              class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
            >
              Back to Purchase Orders
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
  purchaseOrder: {
    type: Object,
    required: true
  }
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat(`en-US`, {
    style: `currency`,
    currency: `USD`
  }).format(amount)
}

const calculateTotal = () => {
  return props.purchaseOrder.po_products.reduce((total, product) => {
    return total + (product.quantity * product.price)
  }, 0)
}
</script>