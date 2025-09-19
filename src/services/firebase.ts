import { initializeApp } from "firebase/app";
import { getDatabase } from "firebase/database";

// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyBqAAbVDqQ5INdLJD6YnUcR3JrZJZrDKuw",
  authDomain: "orders-assignment-d48a4.firebaseapp.com",
  projectId: "orders-assignment-d48a4",
  storageBucket: "orders-assignment-d48a4.firebasestorage.app",
  messagingSenderId: "223528008815",
  appId: "1:223528008815:web:c88a218b11e45d80c1a19a",
  databaseURL: "https://orders-assignment-d48a4-default-rtdb.firebaseio.com/",
};

const app = initializeApp(firebaseConfig);
export const RTDB = getDatabase(app);