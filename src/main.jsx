import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { createBrowserRouter, RouterProvider } from "react-router";
import JwtContextProvider from "./contexts/JwtContext.jsx";
import "./index.css";
import PageAccueil from "./pages/pageAccueil/PageAccueil.jsx";
import PageTasks from "./pages/pageTasks/PageTasks.jsx";

const router = createBrowserRouter([
  {
    path: "/",
    element: <PageAccueil />,
    errorElement: <p>404</p>,
  },
  {
    path: "/tasks",
    element: <PageTasks />,
    errorElement: <p>Oups</p>,
  },
]);

createRoot(document.getElementById("root")).render(
  <StrictMode>
    <JwtContextProvider>
      <RouterProvider router={router} />
    </JwtContextProvider>
  </StrictMode>,
);
