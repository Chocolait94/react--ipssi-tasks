import { useContext, useState } from "react";
import Header from "../../components/Header";
import { JwtContext } from "../../contexts/JwtContext";

const PageAccueil = () => {
  // logique ici
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState(null);

  const { setJwt } = useContext(JwtContext);

  const handleChangeEmail = (event) => {
    setEmail(event.target.value);
  };

  const handleChangePassword = (event) => {
    setPassword(event.target.value);
  };

  const handleLogin = async (event) => {
    event.preventDefault();

    console.log(password, email);
    setError(null);

    if (password === "") {
      setError("Merci de saisir votre mot de passe");
      return;
    } else if (password.length < 6) {
      setError("Pas assez de caractères a votre MDP !");
      return;
    }

    try {
      const request = await fetch("http://localhost:8000/api/login", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          email: email,
          password: password,
        }),
      });

      const response = await request.json();

      console.log(response);
      if (response.code === 401) {
        console.log("mauvais credentials");
        setError("Il y a un problème avec votre email / votre mot de passe :(");
      }

      setJwt(response.token);
    } catch (error) {
      console.log(error);
    }
  };
  // rendering ici (HTML + un tout petit de JS si besoin)
  return (
    <>
      <p>Page Accueil</p>
      <Header />
      <form action="" style={{ margin: "20px" }}>
        <div>
          <label htmlFor="email">Merci de saisir de votre email</label>
          <input
            type="email"
            name="email"
            id="email"
            value={email}
            onChange={handleChangeEmail}
          />
        </div>
        <div>
          <label htmlFor="password">
            Merci de saisir de votre mot de passe
          </label>
          <input
            type="password"
            name="password"
            id="password"
            onChange={handleChangePassword}
            value={password}
          />
        </div>
        <button onClick={handleLogin}>Se connecter</button>
      </form>
      {error !== null ? <div>{error}</div> : null}
    </>
  );
};

export default PageAccueil;
