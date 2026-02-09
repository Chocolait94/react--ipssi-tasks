import { useContext } from "react";
import Header from "../../components/Header";
import { JwtContext } from "../../contexts/JwtContext";

const PageTasks = () => {
  // logique ici
  const { jwt } = useContext(JwtContext);

  // rendering ici (HTML + un tout petit de JS si besoin)
  return (
    <>
      <p>Page Tasks</p>

      <Header />
      {jwt}
    </>
  );
};

export default PageTasks;
