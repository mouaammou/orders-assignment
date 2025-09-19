<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import { RTDB } from "./services/firebase";
import {
  ref as DBref,
  query,
  orderByChild,
  equalTo,
  onValue,
  update,
  push,
} from "firebase/database";
import { computed } from "vue";

interface Order {
  id: string;
  total_price: string;
  placed_at: string;
  delivered_at: string | null;
  cancelled_at: string | null;
  status: "new" | "delivered" | "cancelled";
}

const loading = ref(true);
const newOrders = ref<Order[]>([]);
const allOrders = ref(<Order[]>[]);
const currentPage = ref<number>(1);
const searchValue = ref("");
const isFormOpen = ref(false);
const setOrderPrice = ref(0);
const setOrderDate = ref<string | null>(null);
const error = ref("");
const ordersPerPage: number = 6;

let unsubscribe: (() => void) | null = null;

//  ==> pagination functions
const totalPages = computed(() => {
  return Math.ceil(newOrders.value.length / ordersPerPage);
});

const paginatedOrders = computed(() => {
  const startIndex = (currentPage.value - 1) * ordersPerPage;
  const endIndex = startIndex + ordersPerPage;
  const resultPagination = newOrders.value.slice(startIndex, endIndex);
  console.log(
    `start Index: ${startIndex}, endIndex: ${endIndex}, values founded: ${resultPagination.length}, original values: ${newOrders.value.length}`
  );
  return resultPagination;
});

const nextOrders = () => {
  if (currentPage.value && currentPage.value < totalPages.value) currentPage.value++;
};

const prevOrders = () => {
  if (currentPage.value && currentPage.value > 1) currentPage.value--;
};

// ==> on the component mound, listen for a new update for the firebase realtime DB, and update the UI accourdinally
onMounted(() => {
  //create the query that will search for the /orders/{childrens}/status=new
  const queryNewOrders = query(
    DBref(RTDB, "orders"),
    orderByChild("status"),
    equalTo("new")
  );

  // ==> litener for database update
  unsubscribe = onValue(queryNewOrders, (snapshot) => {
    const data = snapshot.val();

    allOrders.value = [];
    newOrders.value = [];

    data &&
      Object.keys(data).forEach((key) => {
        allOrders.value.push(data[key]);
      });

    newOrders.value = [...allOrders.value];
    loading.value = false;
  });
});

// ==> when the component umnouted
onUnmounted(() => {
  if (unsubscribe) unsubscribe();
});

// ==> buttons [deliver, cancel] functions
const makeOrderCancelled: any = async (orderId: string) => {
  console.log("cancel order: ");
  try {
    const updateOrder = {
      [`orders/${orderId}/status`]: "cancelled",
      [`orders/${orderId}/cancelled_at`]: new Date().toISOString(),
    };
    await update(DBref(RTDB), updateOrder);
    currentPage.value = 1;
  } catch (error) {
    console.error("error happnens in makeOrderCancelled function: ", error);
  }
};

const markOrderDelivered: any = async (orderId: string) => {
  console.log("update order: ");
  try {
    const updateOrder = {
      [`orders/${orderId}/status`]: "delivered",
      [`orders/${orderId}/delivered_at`]: new Date().toISOString(),
    };
    await update(DBref(RTDB), updateOrder);
    currentPage.value = 1;
  } catch (error) {
    console.error("error happnens in markOrderDelivered function: ", error);
  }
};

// ==> search function
const handleSearch = (event: any) => {
  searchValue.value = event.target.value.trim();

  if (!searchValue.value) {
    newOrders.value = [...allOrders.value];
    return;
  }

  searchValue.value &&
    (newOrders.value = [...allOrders.value]) &&
    setTimeout(() => {
      const filteredOrders = allOrders.value.filter((order) => {
        return (
          order.placed_at.includes(searchValue.value) ||
          order.total_price.toString().includes(searchValue.value)
        );
      });

      newOrders.value = filteredOrders;
      currentPage.value = 1;
    }, 400);
};

// ==> date function to make more readable
const makeDateReadable = (dateString: string) => {
  const date = new Date(dateString);
  const readableDate = date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "numeric",
    minute: "numeric",
    hour12: false,
    timeZone: "UTC",
  });
  return readableDate;
};

const toggleForm = () => {
  isFormOpen.value = !isFormOpen.value;
};

const handleAddOrder = async () => {
  error.value = "";
  if (setOrderPrice.value <= 0 || !setOrderDate.value) {
    error.value = "please insert valid data and try again!";
    return;
  }

  const newRefDB = push(DBref(RTDB, 'orders'));
  const orderId = newRefDB.key;
  console.log("key: ", orderId);

  // const orderId = Date.now();
  const orderData = {
    id: orderId,
    total_price: setOrderPrice.value,
    placed_at: setOrderDate.value,
    status: "new",
    delivered_at: null,
    cancelled_at: null,
  };

  await update(DBref(RTDB, `orders/${orderId}`), orderData);
  setOrderPrice.value = 0;
  setOrderDate.value = null;
};
</script>

<template>
  <!-- search feature, you can search by price and date, for date, search by numbers: yyyy or mm or dd -->
  <div class="relative">
    <h1 class="title">orders Menu</h1>
    <div class="order-form-action" @click="toggleForm">
      <span v-if="isFormOpen">x</span>
      <span v-else="isFormOpen">+</span>
    </div>
  </div>

  <div class="relative">
    <div class="order-form" v-if="isFormOpen">
      <h3>NEW ORDER</h3>
      <div>
        <label for="order_price">Price</label>
        <input type="number" name="order_price" v-model="setOrderPrice" />
      </div>
      <div>
        <label for="order_date">Placed At</label>
        <input type="datetime-local" name="order_date" v-model="setOrderDate" />
      </div>
      <div class="text-yellow-600">
        {{ error }}
      </div>
      <button @click="handleAddOrder">Add Order</button>
    </div>

    <div class="search">
      <div>
        <label for="search">search </label>
        <input
          type="text"
          name="search"
          placeholder="search by price, date"
          @input="handleSearch"
        />
      </div>
    </div>
    <!-- loading state -->
    <div v-if="loading">Loading data ...</div>
    <!-- main content: if there is only orders to display -->
    <div v-else-if="newOrders.length">
      <ul class="orders-container">
        <li v-for="(order, index) in paginatedOrders" :key="order.id">
          <div class="order-item">
            <div>
              Status <span> {{ order.status }} </span>
            </div>
            <div>
              Price <span> {{ order.total_price }} MAD</span>
            </div>
            <div>
              Placed_at <span> {{ makeDateReadable(order.placed_at) }} </span>
            </div>
            <button @click="makeOrderCancelled(order.id)" class="mark_cancelled">
              mark as cancelled
            </button>
            <button @click="markOrderDelivered(order.id)">mark as delivered</button>
          </div>
        </li>
      </ul>
      <!-- the pagination available only if the orders iterms more that the ordresPerpage (6) -->
      <div v-if="newOrders.length > ordersPerPage" class="pagination">
        <button @click="prevOrders">Prev</button>
        <p>{{ `${currentPage} / ${totalPages}` }}</p>
        <button @click="nextOrders">Next</button>
      </div>
    </div>
    <!-- if there is no orders or youre search not found -->
    <div v-else>No data available at the moment</div>
  </div>
</template>
