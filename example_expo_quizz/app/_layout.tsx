import { Tabs } from "expo-router";

const RootLayout = () => {
  return (
    <Tabs>
      <Tabs.Screen name="index" options={{ headerShown: false }} />
      <Tabs.Screen name="quizz" options={{ href: "/quizz" }} />
    </Tabs>
  );
};
export default RootLayout;
