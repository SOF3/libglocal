// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.psi;

import java.util.List;
import org.jetbrains.annotations.*;
import com.intellij.psi.PsiElement;

public interface LgcMessageBlock extends PsiElement {

  @NotNull
  List<LgcArgModifier> getArgModifierList();

  @NotNull
  List<LgcDocModifier> getDocModifierList();

  @NotNull
  LgcFullLiteral getFullLiteral();

  @NotNull
  List<LgcVersionModifier> getVersionModifierList();

  @Nullable
  PsiElement getEol();

  @NotNull
  PsiElement getEquals();

  @Nullable
  PsiElement getIndentDecrease();

  @Nullable
  PsiElement getIndentIncrease();

  @NotNull
  PsiElement getMessageId();

}
