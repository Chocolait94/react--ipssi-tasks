import { createContext, useState } from "react";
const JwtContext = createContext(null);

const JwtContextProvider = ({ children }) => {
  const [jwt, setJwt] = useState(null);

  const myJwtElements = {
    jwt: jwt,
    setJwt: setJwt,
  };

  return (
    <JwtContext.Provider value={myJwtElements}>{children}</JwtContext.Provider>
  );
};

export default JwtContextProvider;

export { JwtContext };
