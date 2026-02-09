import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { createBrowserRouter, RouterProvider } from "react-router";
import "./index.css";
import PageAccueil from "./pages/pageAccueil/PageAccueil.jsx";

const router = createBrowserRouter([
  {
    path: "/",
    element: <PageAccueil />,
    errorElement: <p>404</p>,
  },
  {
    path: "/tasks",
    element: <p>Tasks</p>,
  },
]);

createRoot(document.getElementById("root")).render(
  <StrictMode>
    <RouterProvider router={router} />
  </StrictMode>,
);
