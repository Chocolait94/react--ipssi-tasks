import NavLink from "./NavLink";

const Header = () => {
  return (
    <header>
      <nav>
        <NavLink path="/">Accueil</NavLink>
        <NavLink path="/tasks">Voir les tâches en cours</NavLink>
      </nav>
    </header>
  );
};

export default Header;
